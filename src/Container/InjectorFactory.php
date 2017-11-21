<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Container;

use Psr\Container\ContainerInterface;
use Zend\Di\ConfigInterface;
use Zend\Di\Injector;
use Zend\Di\InjectorInterface;

/**
 * Implements the DependencyInjector service factory for zend-servicemanager
 */
class InjectorFactory
{
    /**
     * @param ContainerInterface $container
     * @return ConfigInterface
     */
    private function createConfig(ContainerInterface $container) : ConfigInterface
    {
        if ($container->has(ConfigInterface::class)) {
            return $container->get(ConfigInterface::class);
        }

        return (new ConfigFactory())->create($container);
    }

    /**
     * {@inheritDoc}
     */
    public function create(ContainerInterface $container) : InjectorInterface
    {
        $config = $this->createConfig($container);
        return new Injector($config, $container);
    }

    /**
     * Make the instance invokable
     */
    public function __invoke(ContainerInterface $container) : InjectorInterface
    {
        return $this->create($container);
    }
}
