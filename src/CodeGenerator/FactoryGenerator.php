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
use Zend\Code\Generator\ValueGenerator;
use Zend\Di\ConfigInterface;
use Zend\Di\Resolver\DependencyResolverInterface;
use Zend\Di\Resolver\InjectionInterface;
use Zend\Di\Resolver\TypeInjection;

/**
 * Generates factory classes
 */
class FactoryGenerator
{
    use GeneratorTrait;

    const PARAMETERS_TEMPLATE = <<<__CODE__
        if (empty(\$options)) {
            \$args = [
                %s
            ];
        } else {
            \$args = [
                %s
            ];
        }
__CODE__;

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

    protected function buildClassName(string $name): string
    {
        return preg_replace('~[^a-z0-9\\\\]+~i', '_', $name) . 'Factory';
    }

    protected function buildFileName(string $name): string
    {
        $name = $this->buildClassName($name);
        return str_replace('\\', '/', $name) . '.php';
    }

    private function getClassName(string $type) : string
    {
        if ($this->config->isAlias($type)) {
            return $this->config->getClassForAlias($type);
        }

        return $type;
    }

    /**
     * @param InjectionInterface[] $injections
     */
    private function canGenerateForParameters(iterable $injections): bool
    {
        foreach ($injections as $injection) {
            if (!$injection->isExportable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Builds the code for constructor parameters
     *
     * @param InjectionInterface[] $injections
     */
    private function buildParametersCode(iterable $injections): ?string
    {
        $withOptions = [];
        $withoutOptions = [];

        foreach ($injections as $name => $injection) {
            $code = $injection->export();

            if ($injection instanceof TypeInjection) {
                $code = '$container->get(' . $code . ')';
            }

            // build for two cases:
            // 1. Parameters are passed at call time
            // 2. No Parameters were passed at call time (might be slightly faster)
            $withoutOptions[] = sprintf('%s, // %s', $code, $name);
            $withOptions[]    = sprintf(
                'array_key_exists(%1$s, $options)? $options[%1$s] : %2$s,',
                var_export($name, true),
                $code
            );
        }

        if (! $withOptions) {
            return null;
        }

        $intention = 4;
        $tab = str_repeat(' ', $intention * 4);

        // Build conditional initializer code:
        // If no $params were provided ignore it completely
        // otherwise check if there is a value for each dependency in $params.
        return sprintf(
            self::PARAMETERS_TEMPLATE,
            implode("\n$tab", $withoutOptions),
            implode("\n$tab", $withOptions)
        ) . "\n\n";
    }

    /**
     * @param string $type
     * @return string|false
     */
    private function buildCreateMethodBody(string $type)
    {
        $class = $this->getClassName($type);
        $injections = $this->resolver->resolveParameters($type);

        if (!$this->canGenerateForParameters($injections)) {
            return false;
        }

        $paramsCode = $this->buildParametersCode($injections);
        $absoluteClassName = '\\' . $class;
        $args = ($paramsCode !== null)? '...$args' : '';
        $invokeCode = sprintf('new %s(%s)', $absoluteClassName, $args);

        return $paramsCode . "return $invokeCode;\n";
    }

    private function buildInvokeMethod(ClassGenerator $generator)
    {
        $code = 'if (is_array($name) && ($options === null)) {' . PHP_EOL
            . '    $options = $name;' . PHP_EOL
            . '}' . PHP_EOL . PHP_EOL
            . 'return $this->create($container, $options ?? []);';

        $args = [
            new ParameterGenerator('container', ContainerInterface::class),
            new ParameterGenerator('name', null, new ValueGenerator(null, ValueGenerator::TYPE_NULL)),
            new ParameterGenerator('options', 'array', new ValueGenerator(null, ValueGenerator::TYPE_NULL)),
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
