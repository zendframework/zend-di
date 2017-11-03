<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\Definition;

use Zend\Di\Definition\RuntimeDefinition;
use Zend\Di\Exception;
use ZendTest\Di\TestAsset;
use Zend\Di\Definition\ClassDefinitionInterface;

/**
 * @coversDefaultClass Zend\Di\Definition\RuntimeDefinition
 */
class RuntimeDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testSetExplicitClasses()
    {
        $expected = [
            TestAsset\A::class,
            TestAsset\B::class
        ];

        $definition = new RuntimeDefinition();
        $definition->setExplicitClasses($expected);

        $this->assertEquals($expected, $definition->getClasses());
    }

    public function testSetExplicitClassesViaConstructor()
    {
        $expected = [
            TestAsset\A::class,
            TestAsset\B::class
        ];

        $definition = new RuntimeDefinition($expected);
        $this->assertEquals($expected, $definition->getClasses());
    }

    public function testSetExplicitClassesReplacesPrefiousValues()
    {
        $expected = [
            TestAsset\A::class,
            TestAsset\B::class
        ];

        $definition = new RuntimeDefinition();
        $definition->setExplicitClasses([TestAsset\Parameters::class]);
        $definition->setExplicitClasses($expected);

        $this->assertEquals($expected, $definition->getClasses());
    }

    public function provideExistingClasses()
    {
        return [
            [TestAsset\A::class],
            [TestAsset\B::class],
            [TestAsset\Constructor\NoConstructor::class]
        ];
    }

    public function provideInvalidClasses()
    {
        return [
            [TestAsset\DummyInterface::class],
            ['No\\Such\\Class.Because.Bad.Naming']
        ];
    }

    /**
     * @dataProvider provideInvalidClasses
     */
    public function testSetInvalidExplicitClassThrowsException($class)
    {
        $definition = new RuntimeDefinition();

        $this->setExpectedException(Exception\ClassNotFoundException::class);
        $definition->setExplicitClasses([ $class ]);
    }

    /**
     * Tests RuntimeDefinition->addExplicitClass()
     */
    public function testAddExplicitClass()
    {
        $expected = [
            TestAsset\A::class,
            TestAsset\B::class
        ];

        $definition = new RuntimeDefinition();
        $definition->setExplicitClasses([TestAsset\A::class]);
        $definition->addExplicitClass(TestAsset\B::class);

        $this->assertEquals($expected, $definition->getClasses());
    }

    /**
     * @dataProvider provideInvalidClasses
     */
    public function testAddInvalidExplicitClassThrowsException($class)
    {
        $definition = new RuntimeDefinition();

        $this->setExpectedException(Exception\ClassNotFoundException::class);
        $definition->addExplicitClass($class);
    }

    /**
     * @dataProvider provideExistingClasses
     */
    public function testHasClassReturnsTrueDynamically($class)
    {
        $this->assertTrue(
            (new RuntimeDefinition())->hasClass($class)
        );
    }

    /**
     * @dataProvider provideInvalidClasses
     */
    public function testHasClassReturnsFalseForInvalidClasses($class)
    {
        $this->assertFalse(
            (new RuntimeDefinition())->hasClass($class)
        );
    }

    /**
     * @dataProvider provideExistingClasses
     */
    public function testGetClassDefinition($class)
    {
        $definition = new RuntimeDefinition();
        $result = $definition->getClassDefinition($class);

        $this->assertInstanceOf(ClassDefinitionInterface::class, $result);
        $this->assertInstanceOf(\ReflectionClass::class, $result->getReflection());
        $this->assertSame($class, $result->getReflection()->name);
    }

    /**
     * @dataProvider provideExistingClasses
     */
    public function testGetClassDefinitionAutoPopulatesClass($class)
    {
        $definition = new RuntimeDefinition();

        $this->assertSame([], $definition->getClasses());
        $definition->getClassDefinition($class);
        $this->assertEquals([$class], $definition->getClasses());
    }
}
