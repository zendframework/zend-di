<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\Definition\Reflection;

use ReflectionClass;
use ZendTest\Di\TestAsset;

trait ParameterTestTrait
{
    private function reflectAsset($methodName, $parameterIndex = 0)
    {
        $all = (new ReflectionClass(TestAsset\Parameters::class))->getMethod($methodName)->getParameters();
        return $all[$parameterIndex];
    }

    private function buildReflectionArgsFromClass($classname)
    {
        $class = new ReflectionClass($classname);
        $invocationArgs = [];

        /** @var \ReflectionMethod $method */
        foreach ($class->getMethods() as $method) {
            $params = $method->getParameters();
            $typename = substr($method->name, 0, -4);
            $invocationArgs[$typename] = [ $params[0], $typename ];
        }

        return $invocationArgs;
    }

    public function provideBuiltinTypehintedReflections()
    {
        return $this->buildReflectionArgsFromClass(TestAsset\BuiltinTypehintParameters::class);
    }

    public function provideTypehintedParameterReflections()
    {
        return [
            'required' => [$this->reflectAsset('typehintRequired'), TestAsset\A::class],
            'optional' => [$this->reflectAsset('typehintOptional'), TestAsset\A::class],
        ];
    }

    public function provideTypelessParameterReflections()
    {
        return [
            'required' => [$this->reflectAsset('typelessRequired')],
            'optional' => [$this->reflectAsset('typelessOptional')],
        ];
    }
}
