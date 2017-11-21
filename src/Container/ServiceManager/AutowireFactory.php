<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Container\ServiceManager;

use Interop\Container\ContainerInterface;
use Zend\Di\Container\AutowireFactory as GenericAutowireFactory;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instances with autowiring
 *
 * This class is purely for compatibility with Zend\ServiceManager interface which requires container-interop
 */
class AutowireFactory implements AbstractFactoryInterface
{
    /**
     * @var GenericAutowireFactory
     */
    private $factory;

    public function __construct(GenericAutowireFactory $factory = null)
    {
        $this->factory = $factory ? : new GenericAutowireFactory();
    }

    /**
     * Check creatability of the requested name
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $this->factory->canCreate($container, $requestedName);
    }

    /**
     * Make invokable and implement the zend-service factory pattern
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->factory->create($container, (string) $requestedName, $options);
    }
}
