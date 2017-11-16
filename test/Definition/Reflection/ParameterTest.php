<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Definition\Reflection;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionParameter;
use Zend\Code\Reflection\ClassReflection;
use Zend\Di\Definition\Reflection\Parameter;
use ZendTest\Di\TestAsset;

/**
 * Parameter test case.
 */
class ParameterTest extends TestCase
{
    use ParameterTestTrait;

    public function provideGeneralParameters()
    {
        $params = (new ReflectionClass(TestAsset\Parameters::class))->getMethod('general')->getParameters();

        return [
            'notype' => [ $params[0], 'a', 0, true, null ],
            'classhint' => [ $params[1], 'b', 1, true, null ],
            'optional' => [ $params[2], 'c', 2, false, 'something' ]
        ];
    }

    /**
     * @dataProvider provideGeneralParameters
     */
    public function testParamterReflectedCorrectly(
        ReflectionParameter $reflection,
        $expectedName,
        $expectedPosition,
        $expectRequired,
        $expectedDefault
    ) {
        $instance = new Parameter($reflection);

        $this->assertSame($expectedName, $instance->getName());
        $this->assertSame($expectedPosition, $instance->getPosition());

        if ($expectRequired) {
            $this->assertTrue($instance->isRequired(), 'Parameter is expected to be required');
        } else {
            $this->assertFalse($instance->isRequired(), 'Param is not expected to be required');
            $this->assertSame($expectedDefault, $instance->getDefault());
        }
    }

    /**
     * @dataProvider provideTypehintedParameterReflections
     */
    public function testTypehintedParameter(ReflectionParameter $reflection, $expectedType)
    {
        $required = new Parameter($reflection);
        $this->assertSame($expectedType, $required->getType());
        $this->assertFalse($required->isBuiltin());
    }

    /**
     * @dataProvider provideTypelessParameterReflections
     */
    public function testTypelessParamter(ReflectionParameter $reflection)
    {
        $param = new Parameter($reflection);
        $this->assertNull($param->getType(), 'Parameter type must be null');
        $this->assertFalse($param->isBuiltin(), 'Parameter must not be exposed builtin');
    }

    public function provideScalarTypehintedReflections()
    {
        return $this->buildReflectionArgsFromClass(TestAsset\ScalarTypehintParameters::class);
    }

    /**
     * @dataProvider provideBuiltinTypehintedReflections
     */
    public function testBuiltinTypehintedParameters(ReflectionParameter $reflection, $expectedType)
    {
        $param = new Parameter($reflection);
        $this->assertTrue($param->isBuiltin());
        $this->assertSame($expectedType, $param->getType());
    }

    /**
     * @dataProvider provideScalarTypehintedReflections
     */
    public function testScalarTypehintedParameters(ReflectionParameter $reflection, $expectedType)
    {
        $param = new Parameter($reflection);
        $this->assertTrue($param->isBuiltin());
        $this->assertSame($expectedType, $param->getType());
    }

    public function testIterablePseudoType()
    {
        $reflections = (new ClassReflection(TestAsset\IterableDependency::class))->getConstructor()->getParameters();
        $param = new Parameter($reflections[0]);

        $this->assertTrue($param->isBuiltin());
        $this->assertSame('iterable', $param->getType());
    }
}
