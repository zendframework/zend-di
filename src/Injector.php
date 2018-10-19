<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zend\Di\Resolver\InjectionInterface;
use Zend\Di\Resolver\TypeInjection;

use function in_array;

/**
 * Dependency injector that can generate instances using class definitions and configured instance parameters
 */
class Injector implements InjectorInterface
{
    /**
     * @var Definition\DefinitionInterface
     */
    protected $definition = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @var Resolver\DependencyResolverInterface
     */
    protected $resolver;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string[]
     */
    protected $instantiationStack = [];

    /**
     * Constructor
     *
     * @param ConfigInterface|null $config A custom configuration to utilize. An empty configuration is used
     *      when null is passed or the parameter is omitted.
     * @param ContainerInterface|null $container The IoC container to retrieve dependency instances.
     *      `Zend\Di\DefaultContainer` is used when null is passed or the parameter is omitted.
     * @param Definition\DefinitionInterface $definition A custom definition instance for creating requested instances.
     *      The runtime definition is used when null is passed or the parameter is omitted.
     * @param Resolver\DependencyResolverInterface|null $resolver A custom resolver instance to resolve dependencies.
     *      The default resolver is used when null is passed or the parameter is omitted
     */
    public function __construct(
        ConfigInterface $config = null,
        ContainerInterface $container = null,
        Definition\DefinitionInterface $definition = null,
        Resolver\DependencyResolverInterface $resolver = null
    ) {
        $this->definition = $definition ?: new Definition\RuntimeDefinition();
        $this->config = $config ?: new Config();
        $this->resolver = $resolver ?: new Resolver\DependencyResolver($this->definition, $this->config);
        $this->setContainer($container ?: new DefaultContainer($this));
    }

    /**
     * Set the ioc container
     *
     * Sets the ioc container to utilize for fetching instances of dependencies
     *
     * @param ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->resolver->setContainer($container);
        $this->container = $container;

        return $this;
    }

    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
     * Returns the class name for the requested type
     */
    private function getClassName(string $type) : string
    {
        if ($this->config->isAlias($type)) {
            return $this->config->getClassForAlias($type);
        }

        return $type;
    }

    /**
     * Check if the given type name can be instantiated
     *
     * This will be the case if the name points to a class.
     */
    public function canCreate(string $name) : bool
    {
        $class = $this->getClassName($name);
        return (class_exists($class) && ! interface_exists($class));
    }

    /**
     * Create the instance with auto wiring
     *
     * @param string $name Class name or service alias
     * @param array $parameters Constructor parameters, keyed by the parameter name.
     * @return object|null
     * @throws Exception\ClassNotFoundException
     * @throws Exception\RuntimeException
     * @throws ContainerExceptionInterface May be thrown at runtime by the IoC container.
     * @throws NotFoundExceptionInterface May be thrown at runtime by the IoC container.
     */
    public function create(string $name, array $parameters = [])
    {
        if (in_array($name, $this->instantiationStack)) {
            throw new Exception\CircularDependencyException(sprintf(
                'Circular dependency: %s -> %s',
                implode(' -> ', $this->instantiationStack),
                $name
            ));
        }

        $this->instantiationStack[] = $name;

        try {
            $instance = $this->createInstance($name, $parameters);
        } finally {
            array_pop($this->instantiationStack);
        }

        return $instance;
    }

    /**
     * Retrieve a class instance based on the type name
     *
     * Any parameters provided will be used as constructor arguments only.
     *
     * @param string $name The type name to instantiate.
     * @param array $params Constructor arguments, keyed by the parameter name.
     * @return object
     * @throws Exception\InvalidCallbackException
     * @throws Exception\ClassNotFoundException
     * @throws ContainerExceptionInterface May be thrown at runtime by the IoC container.
     * @throws NotFoundExceptionInterface May be thrown at runtime by the IoC container.
     */
    protected function createInstance(string $name, array $params)
    {
        $class = $this->getClassName($name);

        if (! $this->definition->hasClass($class)) {
            $aliasMsg = ($name != $class) ? ' (specified by alias ' . $name . ')' : '';
            throw new Exception\ClassNotFoundException(sprintf(
                'Class %s%s could not be located in provided definitions.',
                $class,
                $aliasMsg
            ));
        }

        if (! class_exists($class) || interface_exists($class)) {
            throw new Exception\ClassNotFoundException(sprintf(
                'Class or interface by name %s does not exist',
                $class
            ));
        }

        $callParameters = $this->resolveParameters($name, $params);

        return new $class(...$callParameters);
    }

    /**
     * @return mixed The value to inject into the instance
     */
    private function getInjectionValue(InjectionInterface $injection)
    {
        $container = $this->container;
        $containerTypes = [
            ContainerInterface::class,
            'Interop\Container\ContainerInterface' // Be backwards compatible with interop/container
        ];

        if (($injection instanceof TypeInjection)
            && ! $container->has((string) $injection)
            && in_array((string) $injection, $containerTypes, true)
        ) {
            return $container;
        }

        return $injection->toValue($container);
    }

    /**
     * Resolve parameters
     *
     * At first this method utilizes the resolver to obtain the types to inject.
     * If this was successful (the resolver returned a non-null value), it will use
     * the ioc container to fetch the instances
     *
     * @param string $type The class or alias name to resolve for
     * @param array $params Provided call time parameters
     * @return array The resulting arguments in call order
     * @throws Exception\UndefinedReferenceException When a type cannot be
     *     obtained via the ioc container and the method is required for
     *     injection.
     * @throws Exception\CircularDependencyException When a circular dependency
     *     is detected
     * @throws ContainerExceptionInterface May be thrown at runtime by the IoC container.
     * @throws NotFoundExceptionInterface May be thrown at runtime by the IoC container.
     */
    private function resolveParameters(string $type, array $params = []) : array
    {
        $resolved = $this->resolver->resolveParameters($type, $params);
        $params = [];

        foreach ($resolved as $position => $injection) {
            try {
                $params[] = $this->getInjectionValue($injection);
            } catch (NotFoundExceptionInterface $containerException) {
                throw new Exception\UndefinedReferenceException(
                    $containerException->getMessage(),
                    $containerException->getCode(),
                    $containerException
                );
            }
        }

        return $params;
    }
}
