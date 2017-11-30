<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

/**
 * Implements the config provider for zend-expressive
 */
class ConfigProvider
{
    /**
     * Implements the config provider
     *
     * @return array The configuration for zend-expressive
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencyConfig()
        ];
    }

    /**
     * Returns the dependency (service manager) configuration
     *
     * @return array
     */
    public function getDependencyConfig() : array
    {
        return [
            'factories' => [
                InjectorInterface::class => Container\InjectorFactory::class,
                ConfigInterface::class => Container\ConfigFactory::class
            ],
            'abstract_factories' => [
                Container\ServiceManager\AutowireFactory::class
            ]
        ];
    }
}
