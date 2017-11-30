<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

use Psr\Container\ContainerInterface;

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
     * @param null|DefinitionInterface $definition
     * @param null|InstanceManager $instanceManager
     * @param null|Config $config
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
     *
     * @param string $type
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
     *
     * @param string $name
     * @return bool
     * @see InjectorInterface::canCreate()
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
     * @param array $parameters Constructor paramters
     * @return object|null
     * @throws Exception\ClassNotFoundException
     * @throws Exception\RuntimeException
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
        } catch (\Exception $e) {
            throw $e;
        } finally {
            array_pop($this->instantiationStack);
        }

        return $instance;
    }

    /**
     * Retrieve a class instance based on class name
     *
     * Any parameters provided will be used as constructor/instantiator arguments only.
     *
     * @param string $name The type name to instantiate
     * @param array $params Constructor/instantiator arguments
     * @return object
     * @throws Exception\InvalidCallbackException
     * @throws Exception\ClassNotFoundException
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

        $definition = $this->definition->getClassDefinition($class);
        $callParameters = $this->resolveParameters($name, $params);

        // Hack to avoid Reflection in most common use cases
        switch (count($callParameters)) {
            case 0:
                return new $class();
            case 1:
                return new $class($callParameters[0]);
            case 2:
                return new $class($callParameters[0], $callParameters[1]);
            case 3:
                return new $class($callParameters[0], $callParameters[1], $callParameters[2]);
            default:
                return $definition->getReflection()->newInstanceArgs($callParameters);
        }
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
     */
    private function resolveParameters(string $type, array $params = []) : array
    {
        $resolved = $this->resolver->resolveParameters($type, $params);
        $params = [];
        $container = $this->container;
        $containerTypes = [
            ContainerInterface::class,
            'Interop\Container\ContainerInterface' // Be backwards compatible with interop/container
        ];

        foreach ($resolved as $position => $injection) {
            if ($injection instanceof Resolver\ValueInjection) {
                $params[] = $injection->getValue();
                continue;
            }

            if (! $injection instanceof Resolver\TypeInjection) {
                throw new Exception\UnexpectedValueException(sprintf(
                    'Invalid injection type: %s',
                    is_object($injection) ? get_class($injection) : gettype($injection)
                ));
            }

            $type = $injection->getType();

            if (! $container->has($type)) {
                if (in_array($type, $containerTypes)) {
                    $params[] = $container;
                    continue;
                }

                throw new Exception\UndefinedReferenceException(sprintf(
                    'Could not obtain instance %s from ioc container for parameter %s of type %s',
                    $type,
                    $position,
                    $type
                ));
            }

            $params[] = $container->get($type);
        }

        return $params;
    }
}
