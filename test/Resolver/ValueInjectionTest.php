<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\Resolver;

use Zend\Di\Exception;
use Zend\Di\Resolver\ValueInjection;
use ZendTest\Di\TestAsset;
use PHPUnit_Framework_TestCase as TestCase;


/**
 * @coversDefaultClass Zend\Di\Resolver\ValueInjection
 */
class ValueInjectionTest extends TestCase
{
    private $streamFixture = null;

    protected function setUp()
    {
        parent::setUp();

        if (!$this->streamFixture) {
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

    public function provideConstructionValues()
    {
        return [
            ['Hello World'],
            [true],
            [7364234],
            [new \stdClass()]
        ];
    }

    /**
     * @dataProvider provideConstructionValues
     */
    public function testSetStateConstructsInstance($value)
    {
        $result = ValueInjection::__set_state(['value' => $value]);
        $this->assertInstanceOf(ValueInjection::class, $result);
        $this->assertSame($value, $result->getValue());
    }

    public function provideExportableValues()
    {
        return [
            ['Testvalue'],
            [124342],
            [uniqid()],
            [time()],
            [true],
            [false],
            [null],
            [microtime(true)],
            [new TestAsset\Resolver\ExportableValue()]
        ];
    }

    public function provideUnexportableItems()
    {
        if (!$this->streamFixture) {
            $this->streamFixture = fopen('php://temp', 'w+');
        }

        return [
            [$this->streamFixture],
            [new TestAsset\Resolver\UnexportableValue1()],
            [new TestAsset\Resolver\UnexportableValue2()],
        ];
    }

    /**
     * @dataProvider provideUnexportableItems
     */
    public function testExportThrowsExceptionForUnexportable($value)
    {
        $instance = new ValueInjection($value);

        $this->setExpectedException(Exception\RuntimeException::class);
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
}
