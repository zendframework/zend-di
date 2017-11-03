<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
    public function __invoke()
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
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                InjectorInterface::class => Container\InjectorFactory::class,
                ConfigInterface::class => Container\ConfigFactory::class
            ],
            'abstract_factories' => [
                Container\AutowireFactory::class
            ]
        ];
    }
}
