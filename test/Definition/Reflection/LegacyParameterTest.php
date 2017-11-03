<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\Definition\Reflection;

use Zend\Di\Definition\Reflection\LegacyParameter;
use ZendTest\Di\TestAsset;

/**
 * LegacyParameter test case.
 * @coversDefaultClass Zend\Di\Definition\Reflection\LegacyParameter
 */
class LegacyParameterTest extends \PHPUnit_Framework_TestCase
{
    use ParameterTestTrait;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        if (version_compare(PHP_VERSION, '7', '>=')) {
            $this->markTestSkipped('This test can only performed on PHP < 7');
        }
    }

    /**
     * @param string $methodName
     * @param number $parameterIndex
     * @return mixed
     */
    private function reflectAsset($methodName, $parameterIndex = 0)
    {
        $all = (new \ReflectionClass(TestAsset\Parameters::class))->getMethod($methodName)->getParameters();
        return $all[$parameterIndex];
    }

    /**
     * @dataProvider provideTypehintedParameterReflections
     */
    public function testTypehintedParameter(\ReflectionParameter $reflection, $expectedType)
    {
        $required = new LegacyParameter($reflection);
        $this->assertSame($expectedType, $required->getType());
        $this->assertFalse($required->isBuiltin());
    }

    /**
     * @dataProvider provideTypelessParameterReflections
     */
    public function testTypelessParamter(\ReflectionParameter $reflection)
    {
        $param = new LegacyParameter($reflection);
        $this->assertNull($param->getType(), 'Parameter type must be null');
        $this->assertFalse($param->isBuiltin(), 'Parameter must not be exposed builtin');
    }

    /**
     * @dataProvider provideBuiltinTypehintedReflections
     */
    public function testIsBuiltin(\ReflectionParameter $reflection, $expectedTypename)
    {
        $param = new LegacyParameter($reflection);
        $this->assertTrue($param->isBuiltin(), 'Parameter is expected to be exposed as builtin');
        $this->assertSame($expectedTypename, $param->getType());
    }
}
