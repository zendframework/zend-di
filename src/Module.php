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
 * Provides Module functionality for Zend Framework 3 applications
 *
 * To add the DI integration to your application use zend frameworks component installer or
 * add `Zend\\Di` to the ZF modules list:
 *
 * ```php
 *  // application.config.php
 *  return [
 *      // ...
 *      'modules' => [
 *          'Zend\\Di',
 *          // ...
 *      ]
 *  ];
 * ```
 */
class Module
{
    /**
     * Returns the configuration for zend-mvc
     *
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
