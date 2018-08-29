<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\CodeGenerator;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use Zend\Di\CodeGenerator\FactoryInterface;
use ZendTest\Di\TestAsset\InvokableInterface;

final class FactoryProphecy implements FactoryInterface
{
    /**
     * @var FactoryInterface|ObjectProphecy
     */
    private static $prophecy = null;

    /**
     * @var InvokableInterface|ObjectProphecy
     */
    private static $constructProphecy = null;

    public static function shutDown()
    {
        self::$constructProphecy = null;
        self:: $prophecy = null;
    }

    /**
     * @return ObjectProphecy|InvokableInterface
     * @throws \ReflectionException
     */
    public static function prophesizeCreation(TestCase $test): ObjectProphecy
    {
        $method = new ReflectionMethod($test, 'prophesize');
        $method->setAccessible(true);
        $prophesize = $method->getClosure($test);

        self::$constructProphecy = $prophesize(InvokableInterface::class);
        self::$prophecy = $prophesize(FactoryInterface::class);
        self::$constructProphecy->__call('__invoke', [])->shouldBeCalled();

        return self::$prophecy;
    }

    public function __construct()
    {
        if (self::$constructProphecy === null) {
            throw new BadMethodCallException(__CLASS__ . ' is not expected to be constructed!');
        }

        self::$constructProphecy->reveal()->__invoke();
    }

    public function create(ContainerInterface $container, array $options)
    {
        return self::$prophecy->reveal()->create($container, $options);
    }
}
