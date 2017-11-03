<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\CodeGenerator;

use Psr\Container\ContainerInterface;
use Zend\Di\InjectorInterface;
use Zend\Di\DefaultContainer;


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
        $this->container = $container? : new DefaultContainer($this);

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
    private function getFactory($type)
    {
        if (is_string($this->factories[$type])) {
            $factory = $this->factories[$type];
            $this->factories[$type] = new $factory();
        }

        return $this->factories[$type];
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\InjectorInterface::canCreate()
     */
    public function canCreate($name)
    {
        return (isset($this->factories[$name]) || $this->injector->canCreate($name));
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\InjectorInterface::create()
     */
    public function create($name, array $options = [])
    {
        if (isset($this->factories[$name])) {
            return $this->getFactory($name)->create($this->container, $options);
        }

        return $this->injector->create($name, $options);
    }
}
