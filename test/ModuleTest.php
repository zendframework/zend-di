<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di;

use PHPUnit\Framework\TestCase;
use Zend\Di\Module;
use Zend\Di\ConfigProvider;

/**
 * @coversDefaultClass Zend\Di\Module
 */
class ModuleTest extends TestCase
{
    public function testModuleProvidesServiceConfiguration()
    {
        $module = new Module();
        $configProvider = new ConfigProvider();

        $config = $module->getConfig();
        $this->assertArrayHasKey('service_manager', $config);
        $this->assertEquals($configProvider->getDependencyConfig(), $config['service_manager']);
    }
}
