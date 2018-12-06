<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di;

use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;
use Zend\Di\CodeGenerator\InjectorGenerator;
use Zend\Di\ConfigInterface;
use Zend\Di\ConfigProvider;
use Zend\Di\InjectorInterface;

/**
 * @coversDefaultClass Zend\Di\Module
 */
class ConfigProviderTest extends TestCase
{
    public function testInstanceIsInvokable() : void
    {
        $this->assertInternalType(IsType::TYPE_CALLABLE, new ConfigProvider());
    }

    public function testProvidesDependencies() : void
    {
        $provider = new ConfigProvider();
        $result   = $provider();

        $this->assertArrayHasKey('dependencies', $result);
        $this->assertEquals($provider->getDependencyConfig(), $result['dependencies']);
    }

    /**
     * Provides service names that should be defined with a factory
     */
    public function provideExpectedServicesWithFactory() : iterable
    {
        return [
            //               service name
            'injector'  => [InjectorInterface::class],
            'config'    => [ConfigInterface::class],
            'generator' => [InjectorGenerator::class],
        ];
    }

    /**
     * @dataProvider provideExpectedServicesWithFactory
     */
    public function testProvidesFactoryDefinition(string $serviceName) : void
    {
        $result = (new ConfigProvider())->getDependencyConfig();
        $this->assertArrayHasKey($serviceName, $result['factories']);
    }
}
