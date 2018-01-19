<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\CodeGenerator;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use Throwable;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Di\ConfigInterface;
use Zend\Di\Definition\DefinitionInterface;
use Zend\Di\Resolver\DependencyResolverInterface;

/**
 * Generator for the dependency injector
 *
 * Generates a Injector class that will use a generated factory for a requested
 * type, if available. This factory will contained pre-resolved dependencies
 * from the provided configuration, definition and resolver instances.
 */
class InjectorGenerator implements LoggerAwareInterface
{
    use GeneratorTrait;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DependencyResolverInterface
     */
    private $resolver;

    /**
     * @deprecated
     * @var DefinitionInterface
     */
    protected $definition;

    /**
     * @var int
     */
    private $factoryIndex = 0;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var FactoryGenerator
     */
    private $factoryGenerator;

    /**
     * @var AutoloadGenerator
     */
    private $autoloadGenerator;

    /**
     * Constructs the compiler instance
     *
     * @param ConfigInterface $config The configuration to compile from
     * @param DependencyResolverInterface $resolver The resolver to utilize
     * @param string $namespace Namespace to use for generated class; defaults
     *     to Zend\Di\Generated.
     */
    public function __construct(
        ConfigInterface $config,
        DependencyResolverInterface $resolver,
        ?string $namespace = null
    ) {
        $this->config = $config;
        $this->resolver = $resolver;
        $this->namespace = $namespace ? : 'Zend\Di\Generated';
        $this->factoryGenerator = new FactoryGenerator($config, $resolver, $this->namespace . '\Factory');
        $this->autoloadGenerator = new AutoloadGenerator($this->namespace);
        $this->logger = new NullLogger();
    }

    /**
     * Generate injector
     *
     * @param array $factories
     */
    private function generateInjector(array $factories)
    {
        $listFile = new FileGenerator();
        $listFile->setFilename($this->outputDirectory . '/factories.php')
            ->setDocBlock(new DocBlockGenerator('AUTO GENERATED FACTORY LIST'))
            ->setBody('return ' . var_export($factories, true) . ';');

        $class = new ClassGenerator('GeneratedInjector', $this->namespace);
        $classFile = new FileGenerator();

        $loadFactoryCode = '$this->factories = require __DIR__ . \'/factories.php\';';
        $class->setExtendedClass('\\' . AbstractInjector::class)
            ->addMethod('loadFactoryList', [], MethodGenerator::FLAG_PUBLIC, $loadFactoryCode);

        $classFile->setFilename($this->outputDirectory . '/GeneratedInjector.php')
            ->setDocBlock(new DocBlockGenerator('AUTO GENERATED DEPENDENCY INJECTOR'))
            ->setNamespace($class->getNamespaceName())
            ->setClass($class);

        $listFile->write();
        $classFile->write();
    }

    /**
     * @param string $class
     * @param array $factories
     */
    private function generateTypeFactory(string $class, array &$factories)
    {
        if (isset($factories[$class])) {
            return;
        }

        $this->logger->debug(sprintf('Generating factory for class "%s"', $class));

        try {
            $factory = $this->factoryGenerator->generate($class);

            if ($factory) {
                $factories[$class] = $factory;
            }
        } catch (Throwable $e) {
            $this->logger->error(sprintf(
                'Could not create factory for "%s": %s',
                $class,
                $e->getMessage()
            ));
        }
    }

    /**
     * @return void
     */
    private function generateAutoload()
    {
        $addFactoryPrefix = function ($value) {
            return 'Factory/' . $value;
        };

        $classmap = array_map($addFactoryPrefix, $this->factoryGenerator->getClassmap());
        $classmap[$this->namespace . '\\GeneratedInjector'] = 'GeneratedInjector.php';

        $this->autoloadGenerator->generate($classmap);
    }

    /**
     * Returns the namespace this generator uses
     */
    public function getNamespace() : string
    {
        return $this->namespace;
    }

    /**
     * Generate the injector
     *
     * This will generate the injector and its factories into the output directory
     *
     * @param string[] $classes
     */
    public function generate($classes = [])
    {
        $this->ensureOutputDirectory();
        $this->factoryGenerator->setOutputDirectory($this->outputDirectory . '/Factory');
        $this->autoloadGenerator->setOutputDirectory($this->outputDirectory);
        $factories = [];

        foreach ($classes as $class) {
            $this->generateTypeFactory((string)$class, $factories);
        }

        foreach ($this->config->getConfiguredTypeNames() as $type) {
            $this->generateTypeFactory($type, $factories);
        }

        $this->generateAutoload();
        $this->generateInjector($factories);
    }
}
