<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di;

use PHPUnit\Framework\TestCase;
use Zend\Di\ConfigProvider;
use PHPUnit\Framework\Constraint\IsType;

/**
 * @coversDefaultClass Zend\Di\Module
 */
class ConfigProviderTest extends TestCase
{
    public function testInstanceIsInvokable()
    {
        $this->assertInternalType(IsType::TYPE_CALLABLE, new ConfigProvider());
    }

    public function testProvidesDependencies()
    {
        $provider = new ConfigProvider();
        $result = $provider();

        $this->assertArrayHasKey('dependencies', $result);
        $this->assertEquals($provider->getDependencyConfig(), $result['dependencies']);
    }
}
