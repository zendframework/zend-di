<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\CodeGenerator;

use Psr\Container\ContainerInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Di\ConfigInterface;
use Zend\Di\Resolver\AbstractInjection;
use Zend\Di\Resolver\DependencyResolverInterface;
use Zend\Di\Resolver\TypeInjection;

/**
 * Generates factory classes
 */
class FactoryGenerator
{
    use GeneratorTrait;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var DependencyResolverInterface
     */
    private $resolver;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $classmap = [];

    /**
     * @param DependencyResolverInterface $resolver
     */
    public function __construct(
        ConfigInterface $config,
        DependencyResolverInterface $resolver,
        ?string $namespace = null
    ) {
        $this->resolver = $resolver;
        $this->config = $config;
        $this->namespace = $namespace ?: 'ZendDiGenerated';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function buildClassName(string $name)
    {
        return preg_replace('~[^a-z0-9\\\\]+~i', '_', $name) . 'Factory';
    }

    /**
     * @param string $name
     * @return string
     */
    protected function buildFileName(string $name)
    {
        $name = $this->buildClassName($name);
        return str_replace('\\', '/', $name) . '.php';
    }

    /**
     * @param string $type
     * @return string|unknown
     */
    private function getClassName(string $type) : string
    {
        if ($this->config->isAlias($type)) {
            return $this->config->getClassForAlias($type);
        }

        return $type;
    }

    /**
     * Builds the code for constructor parameters
     *
     * @param string $type The type name to build for
     */
    private function buildParametersCode(string $type)
    {
        $params = $this->resolver->resolveParameters($type);
        $names = [];

        $withOptions = [];
        $withoutOptions = [];

        /** @var AbstractInjection $injection */
        foreach ($params as $injection) {
            if (! $injection->isExportable()) {
                return false;
            }

            $name = $injection->getParameterName();
            $variable = '$p_' . $name;
            $code = $injection->export();

            if ($injection instanceof TypeInjection) {
                $code = '$container->get(' . $code . ')';
            }

            // build for two cases:
            // 1. Parameters are passed at call time
            // 2. No Parameters were passed at call time (might be slightly faster)
            $names[]          = $variable;
            $withoutOptions[] = sprintf('%s = %s;', $variable, $code);
            $withOptions[]    = sprintf(
                '%1$s = array_key_exists(%3$s, $options)? $options[%3$s] : %2$s;',
                $variable,
                $code,
                var_export($name, true)
            );
        }

        $intention = 4;
        $tab = str_repeat(' ', $intention);
        $code = '';

        if (count($withOptions)) {
            // Build conditional initializer code:
            // If no $params were provided ignore it completely
            // otherwise check if there is a value for each dependency in $params.
            $code = 'if (empty($options)) {' . "\n"
                . $tab . implode("\n$tab", $withoutOptions) . "\n"
                . '} else {' . "\n"
                . $tab . implode("\n$tab", $withOptions)
                . "\n}\n\n";
        }

        return [$names, $code];
    }

    /**
     * @param string $type
     * @return string|false
     */
    private function buildCreateMethodBody(string $type)
    {
        $class = $this->getClassName($type);
        $result = $this->buildParametersCode($type);

        // The resolver was unable to deliver and somehow the instantiator
        // was not considered a requirement. Whatever caused this, it's not acceptable here
        if (! $result) {
            return false;
        }

        list($paramNames, $paramsCode) = $result;

        // Decide if new or static method call should be used
        $absoluteClassName = '\\' . $class;
        $invokeCode = sprintf('new %s(%s)', $absoluteClassName, implode(', ', $paramNames));

        return $paramsCode . "return $invokeCode;\n";
    }

    private function buildInvokeMethod(ClassGenerator $generator)
    {
        $code = 'if (is_string($options)) {' . PHP_EOL
            . '    $options = is_array($zfCompatibleOptions) ? $zfCompatibleOptions : [];' . PHP_EOL
            . '}' . PHP_EOL . PHP_EOL
            . 'return $this->create($container, $options);';

        $args = [
            new ParameterGenerator('container', ContainerInterface::class),
            new ParameterGenerator('options', null, []),
            new ParameterGenerator('zfCompatibleOptions', null, []),
        ];

        $generator->addMethod('__invoke', $args, MethodGenerator::FLAG_PUBLIC, $code);
    }

    /**
     * @param string $class
     * @return bool
     */
    public function generate(string $class)
    {
        $createBody = $this->buildCreateMethodBody($class);

        if (! $createBody || ! $this->outputDirectory) {
            return false;
        }

        $factoryClassName = $this->namespace . '\\' . $this->buildClassName($class);
        $generator = new ClassGenerator($factoryClassName);
        $comment = 'Generated factory for ' . $class;

        $generator->setImplementedInterfaces(['\\' . FactoryInterface::class]);
        $generator->setDocBlock(new DocBlockGenerator($comment));
        $generator->setFinal(true);
        $generator->addMethod('create', [
            new ParameterGenerator('container', ContainerInterface::class),
            new ParameterGenerator('options', 'array', [])
        ], MethodGenerator::FLAG_PUBLIC, $createBody);

        $this->buildInvokeMethod($generator);

        $filename = $this->buildFileName($class);
        $filepath = $this->outputDirectory . '/' . $filename;
        $file = new FileGenerator();

        $this->ensureDirectory(dirname($filepath));

        $file
            ->setFilename($filepath)
            ->setDocBlock(new DocBlockGenerator($comment))
            ->setNamespace($generator->getNamespaceName())
            ->setClass($generator)
            ->write();

        $this->classmap[$factoryClassName] = $filename;
        return $factoryClassName;
    }

    /**
     * @return array
     */
    public function getClassmap() : array
    {
        return $this->classmap;
    }
}
