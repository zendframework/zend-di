<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Container;

use Psr\Container\ContainerInterface;
use Zend\Di\Injector;
use Zend\Di\ConfigInterface;


/**
 * Implements the DependencyInjector service factory for zend-servicemanager
 */
class InjectorFactory
{
    /**
     * @param ContainerInterface $container
     * @return ConfigInterface
     */
    private function createConfig(ContainerInterface $container)
    {
        if ($container->has(ConfigInterface::class)) {
            return $container->get(ConfigInterface::class);
        }

        $factory = new ConfigFactory();
        return $factory($container);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $this->createConfig($container);
        return new Injector($config, null, null, $this);
    }
}
