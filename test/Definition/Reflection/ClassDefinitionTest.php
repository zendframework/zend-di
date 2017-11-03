<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\Definition\Reflection;

use ZendTest\Di\TestAsset\Hierarchy as HierarchyAsset;
use ZendTest\Di\TestAsset\Constructor as ConstructorAsset;
use Zend\Di\Definition\Reflection\ClassDefinition;
use Zend\Di\Definition\ParameterInterface;
use Zend\Di\Definition\Reflection\LegacyParameter;

/**
 * @coversDefaultClass Zend\Di\Definition\Reflection\ClassDefinition
 */
class ClassDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetReflection()
    {
        $result = (new ClassDefinition(HierarchyAsset\A::class))->getReflection();

        $this->assertInstanceOf(\ReflectionClass::class, $result);
        $this->assertEquals(HierarchyAsset\A::class, $result->getName());
    }

    public function testGetSupertypesReturnsAllClasses()
    {
        $supertypes = (new ClassDefinition(HierarchyAsset\C::class))->getSupertypes();
        $expected = [
            HierarchyAsset\A::class,
            HierarchyAsset\B::class
        ];

        $this->assertInternalType('array', $supertypes);

        sort($expected);
        sort($supertypes);

        $this->assertEquals($expected, $supertypes);
    }

    public function testGetSupertypesReturnsEmptyArray()
    {
        $supertypes = (new ClassDefinition(HierarchyAsset\A::class))->getSupertypes();

        $this->assertInternalType('array', $supertypes);
        $this->assertEmpty($supertypes);
    }

    /**
     * Tests ClassDefinition->getInterfaces()
     */
    public function testGetInterfacesReturnsAllInterfaces()
    {
        $result = (new ClassDefinition(HierarchyAsset\C::class))->getInterfaces();
        $expected = [
            HierarchyAsset\InterfaceA::class,
            HierarchyAsset\InterfaceB::class,
            HierarchyAsset\InterfaceC::class
        ];

        $this->assertInternalType('array', $result);

        sort($result);
        sort($expected);

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests ClassDefinition->getInterfaces()
     */
    public function testGetInterfacesReturnsArray()
    {
        $result = (new ClassDefinition(HierarchyAsset\A::class))->getInterfaces();

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function provideClassesWithParameters()
    {
        return [
            [ConstructorAsset\OptionalArguments::class, 2],
            [ConstructorAsset\RequiredArguments::class, 3]
        ];
    }

    /**
     * @dataProvider provideClassesWithParameters
     */
    public function testGetParametersReturnsAllParameters($class, $expectedItemCount)
    {
        $result = (new ClassDefinition($class))->getParameters();

        $this->assertInternalType('array', $result);
        $this->assertCount($expectedItemCount, $result);
        $this->assertContainsOnlyInstancesOf(ParameterInterface::class, $result);
    }

    /**
     * @requires PHP 7.0
     */
    public function testGetParametersForPhp7()
    {
        $result = (new ClassDefinition(ConstructorAsset\Php7::class))->getParameters();

        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(ParameterInterface::class, $result);
    }

    public function testGetParametersReturnsLegacyImplementationForPHP5()
    {
        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            return $this->markTestSkipped('This test cannot be performed on php >= 7');
        }

        $result = (new ClassDefinition(ConstructorAsset\RequiredArguments::class))->getParameters();
        $this->assertContainsOnlyInstancesOf(LegacyParameter::class, $result);
    }

    public function provideParameterlessClasses()
    {
        return [
            [ConstructorAsset\EmptyConstructor::class],
            [ConstructorAsset\NoConstructor::class]
        ];
    }

    /**
     * @dataProvider provideParameterlessClasses
     */
    public function testGetParametersReturnsAnArray($class)
    {
        $result = (new ClassDefinition($class))->getParameters();
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
}
