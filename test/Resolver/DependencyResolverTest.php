<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Resolver;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Di\Config;
use Zend\Di\ConfigInterface;
use Zend\Di\Definition\ClassDefinitionInterface;
use Zend\Di\Definition\DefinitionInterface;
use Zend\Di\Definition\ParameterInterface;
use Zend\Di\Definition\RuntimeDefinition;
use Zend\Di\Exception;
use Zend\Di\Resolver\DependencyResolver;
use Zend\Di\Resolver\TypeInjection;
use Zend\Di\Resolver\ValueInjection;
use ZendTest\Di\TestAsset;
use ArrayIterator;
use ArrayObject;
use IteratorAggregate;
use stdClass;

/**
 * @coversDefaultClass Zend\Di\Resolver\DependencyResolver
 */
class DependencyResolverTest extends TestCase
{
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private function getEmptyContainerMock()
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->withAnyParameters()->willReturn(false);
        $container->expects($this->never())->method('get')->withAnyParameters();

        return $container;
    }

    /**
     * @param array $definition
     * @return ParameterInterface
     */
    private function mockParameter($name, $position, array $options)
    {
        $definition = array_merge([
            'default'  => null,
            'type'     => null,
            'builtin'  => false,
            'required' => true,
        ], $options);

        $mock = $this->getMockForAbstractClass(ParameterInterface::class);
        $mock->method('getName')->willReturn($name);
        $mock->method('getPosition')->willReturn($position);
        $mock->method('getDefault')->willReturn($definition['default']);
        $mock->method('getType')->willReturn($definition['type']);
        $mock->method('isBuiltin')->willReturn((bool)$definition['builtin']);
        $mock->method('isRequired')->willReturn((bool)$definition['required']);

        return $mock;
    }

    /**
     * @return ClassDefinitionInterface
     */
    private function mockClassDefinition($name, array $parameters = [], array $interfaces = [], array $supertypes = [])
    {
        $mock = $this->getMockForAbstractClass(ClassDefinitionInterface::class);

        $mock->method('getInterfaces')->willReturn($interfaces);
        $mock->method('getSupertypes')->willReturn($supertypes);
        $mock->expects($this->never())->method('getReflection');

        $position = 0;
        $paramMocks = [];

        foreach ($parameters as $name => $options) {
            $paramMocks[] = $this->mockParameter($name, $position++, $options);
        }

        $mock->method('getParameters')->willReturn($paramMocks);

        return $mock;
    }

    /**
     * input:
     *
     * [
     *      'Classname' => [
     *          'interfaces' => [ 'Interface', 'interface2', ... ],
     *          'supertypes' => [ 'Supertype1', 'Supertype2', ... ],
     *          'parameters' => [
     *              'paramName' => [
     *                  'required' => true,
     *                  'builtin'  => true,
     *                  'type'     => 'string',
     *                  'default'  => null,
     *              ],
     *              // ...
     *          ],
     *      ],
     *      // ...
     * ]
     *
     * @return DefinitionInterface
     */
    private function mockDefintition(array $definition)
    {
        $mock = $this->getMockForAbstractClass(DefinitionInterface::class);

        $mock->method('getClasses')->willReturn(array_keys($definition));
        foreach ($definition as $class => $options) {
            $options = array_merge([
                'parameters' => [],
                'interfaces' => [],
                'supertypes' => [],
            ], $options);

            $mock->method('getClassDefinition')
                ->with($class)
                ->willReturn($this->mockClassDefinition(
                    $class,
                    $options['parameters'],
                    $options['interfaces'],
                    $options['supertypes']
                ));
        }

        $mock->method('hasClass')->willReturnCallback(function ($class) use ($definition) {
            return isset($definition[$class]);
        });

        return $mock;
    }

    public function testResolveWithoutConfig()
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());

        $params = $resolver->resolveParameters(TestAsset\B::class);
        $this->assertCount(1, $params);

        $injection = array_shift($params);
        $this->assertInstanceOf(TypeInjection::class, $injection);
        $this->assertEquals(TestAsset\A::class, $injection->getType());

        $params = $resolver->resolveParameters(TestAsset\A::class);
        $this->assertInternalType('array', $params);
        $this->assertCount(0, $params);
    }

    public function testResolveWithContainerFailsWhenMissing()
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());

        $this->expectException(Exception\MissingPropertyException::class);
        $resolver->setContainer($this->getEmptyContainerMock());
        $resolver->resolveParameters(TestAsset\RequiresA::class);
    }

    public function testResolveSucceedsWithoutContainer()
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());
        $result = $resolver->resolveParameters(TestAsset\RequiresA::class);

        $this->assertCount(1, $result);
        $this->assertInternalType('array', $result);
        $this->assertSame(TestAsset\A::class, $result['p']->getType());
    }

    public function testResolveFailsForDependenciesWithoutType()
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());

        $this->expectException(Exception\MissingPropertyException::class);
        $resolver->resolveParameters(TestAsset\Constructor\RequiredArguments::class);
    }

    public function testResolveFailsForInterfaces()
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());

        $this->expectException(Exception\ClassNotFoundException::class);
        $resolver->resolveParameters(TestAsset\DummyInterface::class);
    }

    public function provideClassesWithoutConstructionParams()
    {
        return [
            'noargs' => [TestAsset\Constructor\EmptyConstructor::class],
            'noconstruct' => [TestAsset\Constructor\NoConstructor::class]
        ];
    }

    /**
     * @dataProvider provideClassesWithoutConstructionParams
     */
    public function testResolveClassWithoutParameters($class)
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());
        $result = $resolver->resolveParameters($class);

        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    public function testResolveWithOptionalArgs()
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());
        $result = $resolver->resolveParameters(TestAsset\Constructor\OptionalArguments::class);

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ValueInjection::class, $result);
        $this->assertSame(null, $result['foo']->getValue());
        $this->assertSame('something', $result['bar']->getValue());
    }

    public function testResolvePassedDependenciesWithoutType()
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());

        $expected = 'Some Value';
        $result = $resolver->resolveParameters(TestAsset\Constructor\RequiredArguments::class, [
            'anyDep' => $expected
        ]);

        $this->assertCount(3, $result);
        $this->assertInstanceOf(ValueInjection::class, $result['anyDep']);
        $this->assertSame($expected, $result['anyDep']->getValue());
    }

    public function providePreferenceConfigs()
    {
        $args = [];

        foreach (glob(__DIR__ . '/../_files/preferences/*.php') as $configFile) {
            $config = include $configFile;
            $configInstance = new Config($config);
            $name = basename($configFile, 'php');

            foreach ($config['expect'] as $key => $expectation) {
                list($requested, $expectedResult, $context) = $expectation;
                $args[$name . $key] = [
                    $configInstance,
                    $requested,
                    $context,
                    $expectedResult,
                ];
            }
        }

        return $args;
    }

    /**
     * @dataProvider providePreferenceConfigs
     */
    public function testResolveConfiguredPreference(ConfigInterface $config, $requestClass, $context, $expectedType)
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), $config);
        $this->assertSame($expectedType, $resolver->resolvePreference($requestClass, $context));
    }

    public function provideExplicitInjections()
    {
        return [
            'type'  => [new TypeInjection(TestAsset\B::class)],
            'value' => [new ValueInjection(new stdClass())],
        ];
    }

    /**
     * @dataProvider provideExplicitInjections
     */
    public function testExplicitInjectionInConfigIsUsedWithoutAdditionalTypeChecks($expected)
    {
        $config = new Config([
            'types' => [
                TestAsset\RequiresA::class => [
                    'parameters' => [
                        'p' => $expected,
                    ],
                ],
            ],
        ]);

        $resolver = new DependencyResolver(new RuntimeDefinition(), $config);
        $result = $resolver->resolveParameters(TestAsset\RequiresA::class);
        $this->assertArrayHasKey('p', $result);
        $this->assertSame($expected, $result['p']);
    }

    public function provideUnusableParametersData()
    {
        return [
            //            [type,               value,                builtIn]
            'string'   => ['string',           123,                  true],
            'int'      => ['int',              'non-numeric value',  true],
            'bool'     => ['bool',             'non boolean string', true],
            'iterable' => ['iterable',         new stdClass(),       true],
            'callable' => ['callable',         new stdClass(),       true],
            'class'    => [TestAsset\A::class, new stdClass(),       false],
        ];
    }

    /**
     * @dataProvider provideUnusableParametersData
     */
    public function testUnusableConfigParametersThrowsException(string $type, $value, bool $builtin = false)
    {
        $class = uniqid('MockedTestClass');
        $paramName = uniqid('param');
        $config = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();
        $definition = $this->mockDefintition([
            $class => [
                'parameters' => [
                    $paramName => [
                        'type' => $type,
                        'builtin' => $builtin,
                    ],
                ],
            ],
        ]);

        $config->method('isAlias')->willReturn(false);
        $config->expects($this->atLeastOnce())
            ->method('getParameters')
            ->with($class)
            ->willReturn([
                $paramName => $value,
            ]);

        $resolver = new DependencyResolver($definition, $config);

        $this->expectException(Exception\UnexpectedValueException::class);
        $resolver->resolveParameters($class);
    }

    public function provideUsableParametersData()
    {
        // @codingStandardsIgnoreStart
        return [
            //                             [type,               value,                         builtIn]
            'string'                    => ['string',           '123',                         true],
            'int'                       => ['int',              rand(0, 72649), true],
            'floatForInt'               => ['int',              (float) rand(0, 72649) / 10.0, true],
            'intForFloat'               => ['float',            rand(0, 72649), true],
            'float'                     => ['float',            (float) rand(0, 72649) / 10.0, true],

            // Accepted by php as well
            'stringForInt'              => ['int',              '123',                         true],
            'stringForFloat'            => ['float',            '123.78',                      true],

            'boolTrue'                  => ['bool',             false,                         true],
            'boolFalse'                 => ['bool',             true,                          true],
            'iterableArray'             => ['iterable',         [],                            true],
            'iterableIterator'          => ['iterable',         new ArrayIterator([]),         true],
            'iterableIteratorAggregate' => ['iterable',         new class implements IteratorAggregate {
                public function getIterator()
                {
                    return new ArrayIterator([]);
                }
            }, true],
            'callableClosure'           => ['callable',         function () {
            }, true],
            'callableString'            => ['callable',         'trim',                        true],
            'callableObject'            => ['callable',         new class {
                public function __invoke()
                {
                }
            }, true],
            'derivedInstance'           => [TestAsset\B::class, new TestAsset\ExtendedB(new TestAsset\A()), false ],
            'directInstance'            => [TestAsset\A::class, new TestAsset\A(),             false ],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider provideUsableParametersData
     */
    public function testUsableConfigParametersAreAccepted(string $type, $value, bool $builtin = false)
    {
        $class = uniqid('MockedTestClass');
        $paramName = uniqid('param');
        $definition = $this->mockDefintition([
            $class => [
                'parameters' => [
                    $paramName => [
                        'type' => $type,
                        'builtin' => $builtin,
                    ],
                ],
            ],
        ]);

        $config = new Config([
            'types' => [
                $class => [
                    'parameters' => [
                        $paramName => $value,
                    ],
                ],
            ],
        ]);

        $resolver = new DependencyResolver($definition, $config);
        $result = $resolver->resolveParameters($class);

        $this->assertArrayHasKey($paramName, $result);
        $this->assertInstanceOf(ValueInjection::class, $result[$paramName]);
        $this->assertSame($value, $result[$paramName]->getValue());
    }

    /**
     * Use Case:
     *
     * - A class requires an interface "A".
     * - The configuration defines this parameter to inject another interface which extends "A"
     *
     * In this case the resolver must accept it.
     */
    public function testConfiguredExtendedInterfaceParameterSatisfiesRequiredInterfaceType()
    {
        $class = uniqid('MockedTestClass');
        $paramName = uniqid('param');
        $definition = $this->mockDefintition([
            $class => [
                'parameters' => [
                    $paramName => [
                        'type' => TestAsset\Hierarchy\InterfaceA::class,
                    ],
                ],
            ],
        ]);

        $config = new Config([
            'types' => [
                $class => [
                    'parameters' => [
                        $paramName => TestAsset\Hierarchy\InterfaceC::class,
                    ],
                ],
            ],
        ]);

        $resolver = new DependencyResolver($definition, $config);
        $result = $resolver->resolveParameters($class);

        $this->assertArrayHasKey($paramName, $result);
        $this->assertInstanceOf(TypeInjection::class, $result[$paramName]);
        $this->assertEquals(TestAsset\Hierarchy\InterfaceC::class, $result[$paramName]->getType());
    }

    public function provideIterableClassNames()
    {
        return [
            'iterator'          => [TestAsset\Pseudotypes\IteratorImplementation::class],
            'iteratorAggregate' => [TestAsset\Pseudotypes\IteratorAggregateImplementation::class],
            'arrayObject'       => [ArrayObject::class],
            'arrayIterator'     => [ArrayIterator::class],
        ];
    }

    /**
     * Scenario:
     *
     * - A class requires an iterable
     * - The configuration defines this parameter to inject a type that implement Traversable
     *
     * In this case the resolver must accept it.
     *
     * @dataProvider provideIterableClassNames
     */
    public function testConfiguredTraversableTypeParameterSatisfiesIterable($iterableClassName)
    {
        $class = TestAsset\IterableDependency::class;
        $paramName = 'iterator';
        $definition = new RuntimeDefinition();
        $config = new Config([
            'types' => [
                $class => [
                    'parameters' => [
                        $paramName => $iterableClassName,
                    ],
                ],
            ],
        ]);

        $resolver = new DependencyResolver($definition, $config);
        $result = $resolver->resolveParameters($class);

        $this->assertArrayHasKey($paramName, $result);
        $this->assertInstanceOf(TypeInjection::class, $result[$paramName]);
        $this->assertEquals($iterableClassName, $result[$paramName]->getType());
    }

    /**
     * Scenario:
     *
     * - A class requires a callable
     * - The configuration defines this parameter to inject a class that implements __invoke()
     *
     * In this case the resolver must accept it.
     */
    public function testConfiguredInvokableTypeParameterSatisfiesCallable()
    {
        $class = uniqid('MockedTestClass');
        $paramName = uniqid('param');
        $definition = $this->mockDefintition([
            $class => [
                'parameters' => [
                    $paramName => [
                        'type' => 'callable',
                    ],
                ],
            ],
        ]);

        $config = new Config([
            'types' => [
                $class => [
                    'parameters' => [
                        $paramName => TestAsset\Pseudotypes\CallableImplementation::class,
                    ],
                ],
                'Callable.Alias' => [
                    'typeOf' => TestAsset\Pseudotypes\CallableImplementation::class,
                ],
            ],
        ]);

        $resolver = new DependencyResolver($definition, $config);
        $result = $resolver->resolveParameters($class);

        $this->assertArrayHasKey($paramName, $result);
        $this->assertInstanceOf(TypeInjection::class, $result[$paramName]);
        $this->assertEquals(TestAsset\Pseudotypes\CallableImplementation::class, $result[$paramName]->getType());
    }

    /**
     * Scenario:
     *
     * - A class requires a callable
     * - The configuration defines this parameter to inject an alias that
     *   points to a class which implements __invoke()
     *
     * In this case the resolver must accept it.
     */
    public function testConfiguredInvokableAliasParameterSatisfiesCallable()
    {
        $class = uniqid('MockedTestClass');
        $paramName = uniqid('param');
        $definition = $this->mockDefintition([
            $class => [
                'parameters' => [
                    $paramName => [
                        'type' => 'callable',
                    ],
                ],
            ],
        ]);

        $config = new Config([
            'types' => [
                $class => [
                    'parameters' => [
                        $paramName => 'Callable.Alias',
                    ],
                ],
                'Callable.Alias' => [
                    'typeOf' => TestAsset\Pseudotypes\CallableImplementation::class,
                ],
            ],
        ]);

        $resolver = new DependencyResolver($definition, $config);
        $result = $resolver->resolveParameters($class);

        $this->assertArrayHasKey($paramName, $result);
        $this->assertInstanceOf(TypeInjection::class, $result[$paramName]);
        $this->assertEquals('Callable.Alias', $result[$paramName]->getType());
    }

    public function testResolvePreferenceUsesSupertypes()
    {
        $definition = new RuntimeDefinition();
        $config = new Config();
        $config->setTypePreference(TestAsset\B::class, TestAsset\ExtendedB::class, TestAsset\Hierarchy\A::class);
        $resolver = new DependencyResolver($definition, $config);

        $this->assertEquals(
            TestAsset\ExtendedB::class,
            $resolver->resolvePreference(TestAsset\B::class, TestAsset\Hierarchy\C::class)
        );
    }

    public function testResolvePreferenceUsesInterfaces()
    {
        $definition = new RuntimeDefinition();
        $config = new Config();
        $config->setTypePreference(
            TestAsset\B::class,
            TestAsset\ExtendedB::class,
            TestAsset\Hierarchy\InterfaceA::class
        );

        $resolver = new DependencyResolver($definition, $config);

        $this->assertEquals(
            TestAsset\ExtendedB::class,
            $resolver->resolvePreference(TestAsset\B::class, TestAsset\Hierarchy\C::class)
        );
    }
}
