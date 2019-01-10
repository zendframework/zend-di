<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\Resolver;

use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use stdClass;
use Zend\Di\Exception;
use Zend\Di\Resolver\InjectionInterface;
use Zend\Di\Resolver\ValueInjection;
use ZendTest\Di\TestAsset;

use function uniqid;

/**
 * @coversDefaultClass Zend\Di\Resolver\ValueInjection
 */
class ValueInjectionTest extends TestCase
{
    private $streamFixture = null;

    protected function setUp()
    {
        parent::setUp();

        if (! $this->streamFixture) {
            $this->streamFixture = fopen('php://temp', 'w+');
        }
    }

    protected function tearDown()
    {
        if ($this->streamFixture) {
            fclose($this->streamFixture);
            $this->streamFixture = null;
        }

        parent::tearDown();
    }

    public function testImplementsContract()
    {
        $this->assertInstanceOf(InjectionInterface::class, new ValueInjection(null));
    }

    public function provideConstructionValues()
    {
        return [
            'string' => ['Hello World'],
            'bool'   => [true],
            'int'    => [7364234],
            'object' => [new stdClass()],
            'null'   => [null],
        ];
    }

    /**
     * @dataProvider provideConstructionValues
     */
    public function testSetStateConstructsInstance($value)
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $result = ValueInjection::__set_state(['value' => $value]);
        $this->assertInstanceOf(ValueInjection::class, $result);
        $this->assertSame($value, $result->toValue($container));
    }

    /**
     * @dataProvider provideConstructionValues
     */
    public function testToValueBypassesContainer($value)
    {
        $result = new ValueInjection($value);
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(Argument::cetera())
            ->shouldNotBeCalled();

        $this->assertSame($value, $result->toValue($container->reveal()));
    }

    public function provideExportableValues()
    {
        return [
            'string'       => ['Testvalue'],
            'int'          => [124342],
            'randomString' => [uniqid()],
            'time'         => [time()],
            'true'         => [true],
            'false'        => [false],
            'null'         => [null],
            'float'        => [microtime(true)],
            'object'       => [new TestAsset\Resolver\ExportableValue()],
            'array'        => [[]],
            'array-string' => [['TestValue', 'OtherValue']],
            'array-int'    => [[123, 456]],
            'array-mixed'  => [
                [
                    new TestAsset\Resolver\ExportableValue(),
                    [1],
                    null,
                    false,
                    true,
                    time(),
                    microtime(true),
                    [[], []],
                    uniqid(),
                    [],
                ],
            ],
        ];
    }

    public function provideUnexportableItems()
    {
        if (! $this->streamFixture) {
            $this->streamFixture = fopen('php://temp', 'w+');
        }

        return [
            'stream'          => [$this->streamFixture],
            'noSetState'      => [new TestAsset\Resolver\UnexportableValue1()],
            'privateSetState' => [new TestAsset\Resolver\UnexportableValue2()],
            'arrayNoSetState' => [[new TestAsset\Resolver\UnexportableValue1()]],
            'arrayPrivateSetState' => [[new TestAsset\Resolver\UnexportableValue2()]],
        ];
    }

    /**
     * @dataProvider provideUnexportableItems
     */
    public function testExportThrowsExceptionForUnexportable($value)
    {
        $instance = new ValueInjection($value);

        $this->expectException(Exception\LogicException::class);
        $instance->export();
    }

    /**
     * @dataProvider provideUnexportableItems
     */
    public function testIsExportableReturnsFalseForUnexportable($value)
    {
        $instance = new ValueInjection($value);
        $this->assertFalse($instance->isExportable());
    }

    /**
     * @dataProvider provideExportableValues
     */
    public function testIsExportableReturnsTrueForExportableValues($value)
    {
        $instance = new ValueInjection($value);
        $this->assertTrue($instance->isExportable());
    }

    /**
     * @dataProvider provideExportableValues
     */
    public function testExportWithExportableValues($value)
    {
        $instance = new ValueInjection($value);
        $result = $instance->export();

        $this->assertInternalType('string', $result, 'Export is expected to return a string value');
        $this->assertNotEquals('', $result, 'The exported value must not be empty');
    }

    public function testGetValueTriggersDeprecatedNotice()
    {
        $value = uniqid();
        $subject = new ValueInjection($value);

        $this->expectException(Deprecated::class);
        self::assertSame($value, $subject->getValue());
    }
}
