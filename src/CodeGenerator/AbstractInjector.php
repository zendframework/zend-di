<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\CodeGenerator;

use Psr\Container\ContainerInterface;
use Zend\Di\DefaultContainer;
use Zend\Di\InjectorInterface;

/**
 * Abstract class for code generated dependency injectors
 */
abstract class AbstractInjector implements InjectorInterface
{
    /**
     * @var string|FactoryInterface[]
     */
    protected $factories = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * {@inheritDoc}
     * @see \Zend\Di\DependencyInjector::__construct()
     */
    public function __construct(InjectorInterface $injector, ContainerInterface $container = null)
    {
        $this->injector = $injector;
        $this->container = $container ? : new DefaultContainer($this);

        $this->loadFactoryList();
    }

    /**
     * Init factory list
     */
    abstract protected function loadFactoryList();

    /**
     * @param string $type
     * @return \Zend\Di\CodeGenerator\FactoryInterface
     */
    private function getFactory($type): FactoryInterface
    {
        if (\is_string($this->factories[$type])) {
            $factory = $this->factories[$type];
            $this->factories[$type] = new $factory();
        }

        return $this->factories[$type];
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\InjectorInterface::canCreate()
     */
    public function canCreate(string $name): bool
    {
        return (isset($this->factories[$name]) || $this->injector->canCreate($name));
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\InjectorInterface::create()
     */
    public function create(string $name, array $options = [])
    {
        if (isset($this->factories[$name])) {
            return $this->getFactory($name)->create($this->container, $options);
        }

        return $this->injector->create($name, $options);
    }
}
