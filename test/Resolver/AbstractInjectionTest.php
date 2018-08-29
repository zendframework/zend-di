<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Resolver;

use Generator;
use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\TestCase;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;
use Zend\Di\Resolver\AbstractInjection;
use function iterator_to_array;

class AbstractInjectionTest extends TestCase
{
    private function createParameterValueStubs(MethodReflection $method): Generator
    {
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->isOptional() || $parameter->isVariadic()) {
                break;
            }

            yield '';
        }
    }

    public function provideImplementedMethodNames(): iterable
    {
        $reflection = new ClassReflection(AbstractInjection::class);

        foreach ($reflection->getMethods() as $method) {
            if ($method->isPublic() &&
                ! $method->isAbstract() &&
                ! $method->isStatic()
            ) {
                $args = iterator_to_array($this->createParameterValueStubs($method));
                yield $method->getName() => [ $method->getName(), $args ];
            }
        }
    }

    /**
     * @dataProvider provideImplementedMethodNames
     */
    public function testImplementedMethodIsDeprecated(string $method, array $args)
    {
        $subject = new class() extends AbstractInjection {
            public function export(): string
            {
            }

            public function isExportable(): bool
            {
            }
        };

        $this->expectException(Deprecated::class);
        $subject->$method(...$args);
    }
}
