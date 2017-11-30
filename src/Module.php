<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

/**
 * Provides Module functionality for Zend Framework 3 applications
 *
 * To add the DI integration to your application use zend frameworks component installer or
 * add `Zend\\Di` to the ZF modules list:
 *
 * <code>
 *  // application.config.php
 *  return [
 *      // ...
 *      'modules' => [
 *          'Zend\\Di',
 *          // ...
 *      ]
 *  ];
 * </code>
 */
class Module
{
    /**
     * Returns the configuration for zend-mvc
     */
    public function getConfig() : array
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
