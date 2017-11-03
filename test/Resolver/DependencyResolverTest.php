<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\Resolver;

use ZendTest\Di\TestAsset;
use Zend\Di\Exception;
use Zend\Di\Config;
use Zend\Di\Definition\RuntimeDefinition;
use Zend\Di\Resolver\DependencyResolver;
use Zend\Di\Resolver\TypeInjection;
use Psr\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Di\ConfigInterface;
use Zend\Di\Definition\DefinitionInterface;
use Zend\Di\Definition\ClassDefinitionInterface;
use Zend\Di\Definition\ParameterInterface;
use Zend\Di\Resolver\ValueInjection;


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
            'default' => null,
            'type' => null,
            'builtin' => false,
            'required' => true
        ], $options);

        $mock = $this->getMockForAbstractClass(ParameterInterface::class);
        $mock->method('getName')->willReturn($name);
        $mock->method('getPosition')->willReturn($position);
        $mock->method('getDefault')->willReturn($definition['default']);
        $mock->method('getType')->willReturn($definition['type']);
        $mock->method('isBuiltin')->willReturn((bool)$definition['builtin']);
        $mock->method('isRequired')->willReturn((bool)$definition['required']);
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
     *                  'builtin' => true,
     *                  'type' => 'string',
     *                  'default' => null
     *              ]
     *              ...
     *          ]
     *      ],
     *      ...
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
                ->willReturn($this->mockClassDefinition($class, $options['parameters'], $options['interfaces'], $options['supertypes']));
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

        $this->setExpectedException(Exception\MissingPropertyException::class);
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

        $this->setExpectedException(Exception\MissingPropertyException::class);
        $resolver->resolveParameters(TestAsset\Constructor\RequiredArguments::class);
    }

    public function testResolveFailsForInterfaces()
    {
        $resolver = new DependencyResolver(new RuntimeDefinition(), new Config());

        $this->setExpectedException(Exception\ClassNotFoundException::class);
        $resolver->resolveParameters(TestAsset\DummyInterface::class);
    }

    public function provideClassesWithoutConstructionParams()
    {
        return [
            [TestAsset\Constructor\EmptyConstructor::class],
            [TestAsset\Constructor\NoConstructor::class]
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

            foreach ($config['expect'] as $expectation) {
                list($requested, $expectedResult, $context) = $expectation;
                $args[] = [
                    $configInstance,
                    $requested,
                    $context,
                    $expectedResult
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
}
