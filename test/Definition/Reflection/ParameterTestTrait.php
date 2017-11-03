<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\Definition\Reflection;

use ZendTest\Di\TestAsset;


trait ParameterTestTrait
{
    private function reflectAsset($methodName, $parameterIndex = 0)
    {
        $all = (new \ReflectionClass(TestAsset\Parameters::class))->getMethod($methodName)->getParameters();
        return $all[$parameterIndex];
    }

    private function buildReflectionArgsFromClass($classname)
    {
        $class = new \ReflectionClass($classname);
        $invocationArgs = [];

        /** @var \ReflectionMethod $method */
        foreach ($class->getMethods() as $method) {
            $params = $method->getParameters();
            $typename = substr($method->name, 0, -4);
            $invocationArgs[] = [ $params[0], $typename ];
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
            [$this->reflectAsset('typehintRequired'), TestAsset\A::class],
            [$this->reflectAsset('typehintOptional'), TestAsset\A::class]
        ];
    }

    public function provideTypelessParameterReflections()
    {
        return [
            [$this->reflectAsset('typelessRequired')],
            [$this->reflectAsset('typelessOptional')]
        ];
    }
}
