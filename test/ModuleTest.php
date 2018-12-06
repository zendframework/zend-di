<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di;

use PHPUnit\Framework\TestCase;
use Zend\Di\ConfigProvider;
use Zend\Di\Module;

/**
 * @coversDefaultClass Zend\Di\Module
 */
class ModuleTest extends TestCase
{
    public function testModuleProvidesServiceConfiguration()
    {
        $module         = new Module();
        $configProvider = new ConfigProvider();

        $config = $module->getConfig();
        $this->assertArrayHasKey('service_manager', $config);
        $this->assertEquals($configProvider->getDependencyConfig(), $config['service_manager']);
    }
}
