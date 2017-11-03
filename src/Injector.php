<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

use Psr\Container\ContainerInterface;


/**
 * Dependency injector that can generate instances using class definitions and configured instance parameters
 */
class Injector implements InjectorInterface
{
    /**
     * @var \Zend\Di\Definition\DefinitionInterface
     */
    protected $definition = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @var \Zend\Di\Resolver\DependencyResolverInterface
     */
    protected $resolver;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string[]
     */
    protected $instanciationStack = [];

    /**
     * Constructor
     *
     * @param null|DefinitionInterface  $definition
     * @param null|InstanceManager      $instanceManager
     * @param null|Config               $config
     */
    public function __construct(ConfigInterface $config = null, ContainerInterface $container = null, Definition\DefinitionInterface $definition = null, Resolver\DependencyResolverInterface $resolver = null)
    {
        $this->definition = $definition? : new Definition\RuntimeDefinition();
        $this->config = $config? : new Config();
        $this->resolver = $resolver? : new Resolver\DependencyResolver($this->definition, $this->config);
        $this->setContainer($container? : new DefaultContainer($this));
    }

    /**
     * Set the ioc container
     *
     * Sets the ioc container to utilize for fetching instances of dependencies
     *
     * @param  ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->resolver->setContainer($container);
        $this->container = $container;

        return $this;
    }

    /**
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the class name for the requested type
     *
     * @param string $type
     */
    private function getClassName($type)
    {
        if ($this->config->isAlias($type)) {
            return $this->config->getClassForAlias($type);
        }

        return $type;
    }

    /**
     * Check if the given type name can be instanciated
     *
     * This will be the case if the name points to a class.
     *
     * @param  string $name
     * @return bool
     * @see    \Zend\Di\InjectorInterface::canCreate()
     */
    public function canCreate($name)
    {
        $class = $this->getClassName($name);
        return (class_exists($class) && !interface_exists($class));
    }

    /**
     * Create the instance with auto wiring
     *
     * @param  string                           $name               Class name or service alias
     * @param  array                            $parameters         Constructor paramters
     * @return object|null
     * @throws Exception\ClassNotFoundException
     * @throws Exception\RuntimeException
     */
    public function create($name, array $parameters = [])
    {
        if (in_array($name, $this->instanciationStack)) {
            throw new Exception\CircularDependencyException(sprintf('Circular dependency: %s -> %s', implode(' -> ', $this->instanciationStack), $name));
        }

        $this->instanciationStack[] = $name;

        try {
            $instance = $this->createInstance($name, $parameters);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            array_pop($this->instanciationStack);
        }

        return $instance;
    }

    /**
     * Retrieve a class instance based on class name
     *
     * Any parameters provided will be used as constructor/instanciator arguments only.
     *
     * @param   string  $name   The type name to instanciate
     * @param   array   $params Constructor/instanciator arguments
     * @return  object
     *
     * @throws  Exception\InvalidCallbackException
     * @throws  Exception\ClassNotFoundException
     */
    protected function createInstance($name, $params)
    {
        $class = $this->getClassName($name);

        if (!$this->definition->hasClass($class)) {
            $aliasMsg = ($name != $class) ? ' (specified by alias ' . $name . ')' : '';
            throw new Exception\ClassNotFoundException('Class ' . $class . $aliasMsg . ' could not be located in provided definitions.');
        }

        if (!class_exists($class) || interface_exists($class)) {
            throw new Exception\ClassNotFoundException($class);
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
     * @param  string                                $type      The class or alias name to resolve for
     * @param  array                                 $params    Provided call time parameters
     * @throws Exception\UndefinedReferenceException            When a type cannot be obtained via the ioc container and the method is required for injection
     * @throws Exception\CircularDependencyException            When a circular dependency is detected
     * @return array|null                                       The resulting arguments in call order or null if nothing could be obtained
     */
    private function resolveParameters($type, array $params = [])
    {
        $resolved = $this->resolver->resolveParameters($type, $params);
        $params = [];
        $container = $this->container;
        $containerTypes = [
            ContainerInterface::class,
            'Interop\Container\ContainerInterface'
        ];

        foreach ($resolved as $position => $injection) {
            if ($injection instanceof Resolver\ValueInjection) {
                $params[] = $injection->getValue();
                continue;
            }

            if (!$injection instanceof Resolver\TypeInjection) {
                throw new Exception\UnexpectedValueException('Invalid injection type: ' . (is_object($injection)? get_class($injection) : gettype($injection)));
            }

            $type = $injection->getType();

            if (!$container->has($type)) {
                if (in_array($type, $containerTypes)) {
                    $params[] = $container;
                    continue;
                }

                throw new Exception\UndefinedReferenceException('Could not obtain instance ' . $type . ' from ioc container for parameter ' . $position . ' of type ' . $type);
            }

            $params[] = $container->get($type);
        }

        return $params;
    }
}
