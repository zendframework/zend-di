<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\Resolver;

use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Zend\Di\Resolver\InjectionInterface;
use Zend\Di\Resolver\TypeInjection;
use Zend\Di\Resolver\ValueInjection;

use function sprintf;
use function uniqid;

/**
 * @covers \Zend\Di\Resolver\TypeInjection
 */
class TypeInjectionTest extends TestCase
{
    public function testImplementsContract()
    {
        $this->assertInstanceOf(InjectionInterface::class, new TypeInjection('typename'));
    }

    public function testToValueUsesContainer()
    {
        $container     = $this->prophesize(ContainerInterface::class);
        $typename      = uniqid('TypeName');
        $expectedValue = new stdClass();
        $subject       = new TypeInjection($typename);

        $container->get($typename)
            ->shouldBeCalled()
            ->willReturn($expectedValue);

        $this->assertSame($expectedValue, $subject->toValue($container->reveal()));
    }

    public function testExport()
    {
        $typename = 'TypeName';
        $expected = sprintf("'%s'", $typename);

        $this->assertSame($expected, (new ValueInjection($typename))->export());
    }

    public function provideTypeNames() : iterable
    {
        return [
            'arbitary' => ['SomeArbitaryTypeName'],
        ];
    }

    /**
     * @dataProvider provideTypeNames
     */
    public function testIsExportableIsAlwaysTrue($typeName)
    {
        $this->assertTrue((new TypeInjection($typeName))->isExportable());
    }

    public function testGetTypeIsDeprectaed()
    {
        $subject = new TypeInjection('SomeType');
        $this->expectException(Deprecated::class);
        $this->assertSame('SomeType', $subject->getType());
    }
}
