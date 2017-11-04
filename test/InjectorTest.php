<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di;

use Zend\Di\Injector;
use Zend\Di\Resolver\DependencyResolverInterface;
use Zend\Di\Config;
use Zend\Di\DefaultContainer;
use Zend\Di\Exception;
use ZendTest\Di\TestAsset\DependencyTree as TreeTestAsset;
use Psr\Container\ContainerInterface;

/**
 * @coversDefaultClass Zend\Di\Injector
 */
class InjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $value
     * @return \PHPUnit_Framework_Constraint_IsIdentical
     */
    private function isIdentical($value)
    {
        return new \PHPUnit_Framework_Constraint_IsIdentical($value);
    }

    public function testSetContainerReplacesConstructed()
    {
        $mock1 = $this->getMockForAbstractClass(ContainerInterface::class);
        $mock2 = $this->getMockForAbstractClass(ContainerInterface::class);

        $injector = new Injector(null, $mock1);
        $injector->setContainer($mock2);

        $this->assertSame($mock2, $injector->getContainer());
        $this->assertNotSame($mock1, $injector->getContainer());
    }

    public function testConstructWithContainerPassesItToResolver()
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $resolver = $this->getMockForAbstractClass(DependencyResolverInterface::class);
        $resolver->expects($this->once())
            ->method('setContainer')
            ->with($this->isIdentical($container))
            ->willReturnSelf();

        $injector = new Injector(null, $container, null, $resolver);
        $this->assertSame($container, $injector->getContainer());
    }

    public function testSetContainerPassesItToResolver()
    {
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $resolver = $this->getMockForAbstractClass(DependencyResolverInterface::class);
        $injector = new Injector(null, null, null, $resolver);

        $resolver->expects($this->once())
            ->method('setContainer')
            ->with($this->isIdentical($container))
            ->willReturnSelf();

        $injector->setContainer($container);
        $this->assertSame($container, $injector->getContainer());
    }

    /**
     * @return string[][]
     */
    public function provideClassNames()
    {
        return [
            [TestAsset\A::class],
            [TestAsset\B::class],
            [TestAsset\Option1ForA::class]
        ];
    }

    /**
     * @dataProvider provideClassNames
     */
    public function testCanCreateReturnsTrueForClasses($className)
    {
        $this->assertTrue((new Injector())->canCreate($className));
    }

    public function testCanCreateReturnsFalseForInterfaces()
    {
        $this->assertFalse((new Injector())->canCreate(TestAsset\DummyInterface::class));
    }

    public function testCanCreateReturnsFalseForNonExistingClassOrAlias()
    {
        $injector = new Injector();
        $this->assertFalse($injector->canCreate('Zend\Di\TestAsset\NoSuchClass'));
        $this->assertFalse($injector->canCreate('Some.Alias.Name'));
    }

    public function provideValidAliases()
    {
        return [
            [ 'Foo.Alias', TestAsset\A::class ],
            [ 'Bar.alias', TestAsset\B::class ],
            [ 'Some.Custom.Name', TestAsset\Constructor\EmptyConstructor::class ]
        ];
    }

    /**
     * @dataProvider provideValidAliases
     */
    public function testCanCreateReturnsTrueWithDefinedAndValidAliases($aliasName, $className)
    {
        $config = new Config([
            'types' => [
                $aliasName => [
                    'typeOf' => $className
                ]
            ]
        ]);

        $this->assertTrue((new Injector($config))->canCreate($aliasName));
    }

    public function testCanCreateReturnsFalseWithDefinedInvalidAliases()
    {
        $config = new Config([
            'types' => [
                'Some.Custom.Name' => [
                    'typeOf' => 'ZendTest\Di\TestAsset\NoSuchClassName'
                ]
            ]
        ]);

        $this->assertFalse((new Injector($config))->canCreate('Some.Custom.Name'));
    }

    public function testCreateWithoutDependencies()
    {
        $result = (new Injector())->create(TestAsset\Constructor\EmptyConstructor::class);
        $this->assertInstanceOf(TestAsset\Constructor\EmptyConstructor::class, $result);
    }

    public function testCreateUsesContainerDependency()
    {
        $injector = new Injector();
        $expectedA = new TestAsset\A();
        $container = new DefaultContainer($injector);

        $container->setInstance(TestAsset\A::class, $expectedA);
        $injector->setContainer($container);

        /** @var \ZendTest\Di\TestAsset\B $result */
        $result = $injector->create(TestAsset\B::class);

        $this->assertInstanceOf(TestAsset\B::class, $result);
        $this->assertSame($expectedA, $result->injectedA);
    }

    public function testCreateSimpleDependency()
    {
        /** @var \ZendTest\Di\TestAsset\B $result */
        $result = (new Injector())->create(TestAsset\B::class);

        $this->assertInstanceOf(TestAsset\B::class, $result);
        $this->assertInstanceOf(TestAsset\A::class, $result->injectedA);
    }

    public function provideCircularClasses()
    {
        $classes = [
            TestAsset\CircularClasses\A::class,
            TestAsset\CircularClasses\B::class,
            TestAsset\CircularClasses\C::class,
            TestAsset\CircularClasses\D::class,
            TestAsset\CircularClasses\E::class,
            TestAsset\CircularClasses\X::class,
            TestAsset\CircularClasses\Y::class,
        ];

        return array_map(function ($class) { return [$class]; }, $classes);
    }

    /**
     * @dataProvider provideCircularClasses
     */
    public function testCircularDependencyThrowsException($class)
    {
        $this->setExpectedException(Exception\CircularDependencyException::class);
        (new Injector())->create($class);
    }

    public function testSimpleTreeResolving()
    {
        /** @var TreeTestAsset\Simple $result */
        $result = (new Injector())->create(TreeTestAsset\Simple::class);
        $this->assertInstanceOf(TreeTestAsset\Simple::class, $result);
        $this->assertInstanceOf(TreeTestAsset\Level1::class, $result->result);
        $this->assertInstanceOf(TreeTestAsset\Level2::class, $result->result->result);
    }

    public function testComplexTreeResolving()
    {
        /** @var TreeTestAsset\Complex $result */
        $result = (new Injector())->create(TreeTestAsset\Complex::class);
        $this->assertInstanceOf(TreeTestAsset\Complex::class, $result);
        $this->assertInstanceOf(TreeTestAsset\Level1::class, $result->result);
        $this->assertInstanceOf(TreeTestAsset\Level2::class, $result->result->result);
        $this->assertInstanceOf(TreeTestAsset\AdditionalLevel1::class, $result->result2);
        $this->assertInstanceOf(TreeTestAsset\Level2::class, $result->result2->result);
        $this->assertSame($result->result->result, $result->result2->result);
    }

    public function testDeepDependencyUsesContainer()
    {
        $injector = new Injector();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);

        // Mocks a container that always creates new instances
        $container->method('has')->willReturnCallback(function ($class) use ($injector) {
            return $injector->canCreate($class);
        });
        $container->method('get')->willReturnCallback(function ($class) use ($injector) {
            return $injector->create($class);
        });

        $injector->setContainer($container);

        $result1 = $injector->create(TreeTestAsset\Complex::class);
        $result2 = $injector->create(TreeTestAsset\Complex::class);

        /** @var TreeTestAsset\Complex $result */
        /** @var TreeTestAsset\Complex $result1 */
        /** @var TreeTestAsset\Complex $result2 */

        foreach ([$result1, $result2] as $result) {
            $this->assertInstanceOf(TreeTestAsset\Complex::class, $result);
            $this->assertInstanceOf(TreeTestAsset\Level1::class, $result->result);
            $this->assertInstanceOf(TreeTestAsset\Level2::class, $result->result->result);
            $this->assertInstanceOf(TreeTestAsset\AdditionalLevel1::class, $result->result2);
            $this->assertInstanceOf(TreeTestAsset\Level2::class, $result->result2->result);
        }

        $this->assertNotSame($result1, $result2);
        $this->assertNotSame($result1->result, $result2->result);
        $this->assertNotSame($result1->result2, $result2->result2);
        $this->assertNotSame($result1->result->result, $result2->result->result);
        $this->assertNotSame($result1->result2->result, $result2->result2->result);

        $this->assertNotSame($result1->result->result, $result1->result2->result);
        $this->assertNotSame($result2->result->result, $result2->result2->result);
    }

    public function testDeepDependencyRespectsGlobalTypePreference()
    {
        $config = new Config([
            'preferences' => [
                TreeTestAsset\Level2::class => TreeTestAsset\Level2Preference::class
            ]
        ]);

        /** @var TreeTestAsset\Complex $result */
        $result = (new Injector($config))->create(TreeTestAsset\Complex::class);
        $this->assertInstanceOf(TreeTestAsset\Level2Preference::class, $result->result2->result);
        $this->assertInstanceOf(TreeTestAsset\Level2Preference::class, $result->result->result);
    }

    public function testDeepDependencyRespectsSpecificTypePreference()
    {
        $config = new Config([
            'types' => [
                TreeTestAsset\AdditionalLevel1::class => [
                    'preferences' => [
                        TreeTestAsset\Level2::class => TreeTestAsset\Level2Preference::class
                    ]
                ]
            ]
        ]);

        /** @var TreeTestAsset\Complex $result */
        $result = (new Injector($config))->create(TreeTestAsset\Complex::class);
        $this->assertInstanceOf(TreeTestAsset\Level2Preference::class, $result->result2->result);
        $this->assertNotInstanceOf(TreeTestAsset\Level2Preference::class, $result->result->result);
    }

    public function testDeepDependencyUsesConfiguredParameters()
    {
        $expected = uniqid('InjectValue');
        $config = new Config([
            'types' => [
                TreeTestAsset\Level2::class => [
                    'parameters' => [
                        'opt' => $expected
                    ]
                ]
            ]
        ]);

        /** @var TreeTestAsset\Complex $result */
        $result = (new Injector($config))->create(TreeTestAsset\Complex::class);
        $this->assertSame($expected, $result->result2->result->optionalResult);
        $this->assertSame($expected, $result->result->result->optionalResult);
    }

    public function testComplexDeepDependencyConfiguration()
    {
        $expected1 = uniqid('InjectValueA');
        $expected2 = uniqid('InjectValueB');

        $config = new Config([
            'types' => [
                TreeTestAsset\Level2::class => [
                    'parameters' => [
                        'opt' => $expected1
                    ]
                ],
                'Level2.Alias' => [
                    'typeOf' => TreeTestAsset\Level2::class,
                    'parameters' => [
                        'opt' => $expected2
                    ]
                ],
                TreeTestAsset\AdditionalLevel1::class => [
                    'preferences' => [
                        TreeTestAsset\Level2::class => 'Level2.Alias'
                    ]
                ]
            ]
        ]);

        /** @var TreeTestAsset\Complex $result */
        $result = (new Injector($config))->create(TreeTestAsset\Complex::class);
        $this->assertSame($expected1, $result->result->result->optionalResult);
        $this->assertSame($expected2, $result->result2->result->optionalResult);
    }
}
