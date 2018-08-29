<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\CodeGenerator;

use PHPUnit\Framework\MockObject\Invokable;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use stdClass;
use function uniqid;
use Zend\Di\CodeGenerator\AbstractInjector;
use PHPUnit\Framework\TestCase;
use Zend\Di\CodeGenerator\FactoryInterface;
use Zend\Di\DefaultContainer;
use Zend\Di\InjectorInterface;
use ZendTest\Di\TestAsset\InvokableInterface;

/**
 * @covers \Zend\Di\CodeGenerator\AbstractInjector
 */
class AbstractInjectorTest extends TestCase
{
    /**
     * @var InjectorInterface|ObjectProphecy
     */
    private $decoratedInjectorProphecy;

    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $containerProphecy;

    protected function setUp()
    {
        $this->decoratedInjectorProphecy = $this->prophesize(InjectorInterface::class);
        $this->containerProphecy = $this->prophesize(ContainerInterface::class);

        parent::setUp();
    }

    public function createTestSubject(callable $factoriesProvider, bool $withContainer = true): AbstractInjector
    {
        $injector = $this->decoratedInjectorProphecy->reveal();
        $container = $withContainer ? $this->containerProphecy->reveal() : null;

        return new class($factoriesProvider, $injector, $container) extends AbstractInjector
        {
            private $provider;

            public function __construct(
                callable $provider,
                InjectorInterface $injector,
                ContainerInterface $container = null
            ) {
                $this->provider = $provider;
                parent::__construct($injector, $container);
            }

            protected function loadFactoryList()
            {
                $this->factories = ($this->provider)();
            }
        };
    }

    public function testImplementsContract()
    {
        $prophecy = $this->prophesize(InvokableInterface::class);
        $prophecy->__call('__invoke', [])
            ->shouldBeCalled()
            ->willReturn([
                'SomeService' => 'SomeFactory'
            ]);

        $subject = $this->createTestSubject($prophecy->reveal());
        $this->assertInstanceOf(InjectorInterface::class, $subject);
    }

    public function testCanCreateReturnsTrueWhenAFactoryIsAvailable()
    {
        $className = uniqid('SomeClass');
        $provider = function () use ($className) {
            return [$className => 'SomeClassFactory'];
        };

        $this->decoratedInjectorProphecy
            ->canCreate($className)
            ->shouldNotBeCalled();

        $subject = $this->createTestSubject($provider);
        $this->assertTrue($subject->canCreate($className));
    }

    public function testCanCreateUsesDecoratedInjectorWithoutFactory()
    {
        $missingClass = uniqid('SomeClass');
        $existingClass = uniqid('SomeOtherClass');
        $provider = function () {
            return [];
        };

        $this->decoratedInjectorProphecy
            ->canCreate($missingClass)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->decoratedInjectorProphecy
            ->canCreate($existingClass)
            ->shouldBeCalled()
            ->willReturn(true);

        $subject = $this->createTestSubject($provider);

        $this->assertTrue($subject->canCreate($existingClass));
        $this->assertFalse($subject->canCreate($missingClass));
    }

    public function testCreateUsesFactory()
    {
        $factory = $this->prophesize(FactoryInterface::class);
        $className = uniqid('SomeClass');
        $params = ['someArg' => uniqid()];
        $expected = new stdClass();
        $provider = function () use ($className, $factory) {
            return [$className => $factory->reveal()];
        };

        $factory->create(
            $this->containerProphecy->reveal(),
            $params
        )
            ->shouldBeCalled()
            ->willReturn($expected);

        $this->decoratedInjectorProphecy
            ->create($className, Argument::cetera())
            ->shouldNotBeCalled();

        $subject = $this->createTestSubject($provider);
        $this->assertSame($expected, $subject->create($className, $params));
    }

    public function testCreateUsesDecoratedInjectorIfNoFactoryIsAvailable()
    {
        $className = uniqid('SomeClass');
        $expected = new stdClass();
        $params = [ 'someArg' => uniqid() ];
        $provider = function () {
            return [];
        };

        $this->decoratedInjectorProphecy->create($className, $params)
            ->shouldBeCalled()
            ->willReturn($expected);

        $subject = $this->createTestSubject($provider);
        $this->assertSame($expected, $subject->create($className, $params));
    }

    public function testConstructionWithoutContainerUsesDefaultContainer()
    {
        $factory = $this->prophesize(FactoryInterface::class);
        $className = uniqid('SomeClass');
        $expected = new stdClass();
        $provider = function () use ($className, $factory) {
            return [$className => $factory->reveal()];
        };

        $factory->create(Argument::type(DefaultContainer::class), Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($expected);

        $subject = $this->createTestSubject($provider, false);
        $this->assertSame($expected, $subject->create($className));
    }

    public function testFactoryIsCreatedFromClassNameString()
    {
        $params = [ 'someOtherArg' => uniqid() ];
        $prophecy = FactoryProphecy::prophesizeCreation($this);
        $expected = new stdClass();
        $prophecy->create($this->containerProphecy->reveal(), $params)
            ->shouldBeCalled()
            ->willReturn($expected);

        $subject = $this->createTestSubject(function () {
            return ['SomeClass' => FactoryProphecy::class ];
        });

        $this->assertSame($expected, $subject->create('SomeClass', $params));
    }
}
