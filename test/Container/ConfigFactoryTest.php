<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\Container;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Deprecated as DeprecatedError;
use Psr\Container\ContainerInterface;
use Zend\Di\Container\ConfigFactory;
use Zend\Di\ConfigInterface;

/**
 * @coversDefaultClass Zend\Di\Container\ConfigFactory
 */
class ConfigFactoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    private $containerBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->containerBuilder = $this->getMockBuilder(ContainerInterface::class);
    }

    protected function tearDown(): void
    {
        $this->containerBuilder = null;

        parent::tearDown();
    }

    public function testInvokeCreatesConfigInstance()
    {
        $container = $this->containerBuilder->getMockForAbstractClass();
        $container->method('has')->willReturn(false);

        $factory = new ConfigFactory();
        $this->assertInstanceOf(ConfigInterface::class, $factory($container));
    }

    /**
     * The factory must succeed even if the container does not provide "config"
     */
    public function testCreateRequestsContainerForConfigServiceGracefully()
    {
        $container = $this->containerBuilder->getMockForAbstractClass();
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $container->expects($this->never())
            ->method('get')
            ->with('config');

        $result = (new ConfigFactory())->create($container);
        $this->assertInstanceOf(ConfigInterface::class, $result);
    }

    private function createContainerWithConfig($config)
    {
        $container = $this->containerBuilder->getMockForAbstractClass();
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        return $container;
    }

    public function testCreateUsesConfigFromContainer()
    {
        $expectedPreference = uniqid('SomePreference');
        $container = $this->createContainerWithConfig([
            'dependencies' => [
                'auto' => [
                    'preferences' => [
                        'SomeDependency' => $expectedPreference,
                    ],
                ],
            ],
        ]);

        $result = (new ConfigFactory())->create($container);
        $this->assertEquals($expectedPreference, $result->getTypePreference('SomeDependency'));
    }

    public function testLegacyConfigIsRespected()
    {
        $expectedPreference = uniqid('SomePreference');
        $container = $this->createContainerWithConfig([
            'di' => [
                'instance' => [
                    'preferences' => [
                        'SomeDependency' => $expectedPreference,
                    ],
                ],
            ],
        ]);

        set_error_handler(function ($errno, $errstr) {
            if ($errno !== \E_USER_DEPRECATED) {
                return false;
            }

            if (! strstr($errstr, 'legacy DI config')) {
                // Not the error we're looking for...
                return false;
            }
        }, \E_USER_DEPRECATED);
        $result = (new ConfigFactory())->create($container);
        restore_error_handler();

        $this->assertEquals($expectedPreference, $result->getTypePreference('SomeDependency'));
    }

    public function testLegacyConfigTriggersDeprecationNotice()
    {
        $container = $this->createContainerWithConfig([
            'di' => [
                'instance' => [],
            ],
        ]);

        $this->expectException(DeprecatedError::class);
        (new ConfigFactory())->create($container);
    }
}
