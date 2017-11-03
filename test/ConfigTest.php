<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di;

use Zend\Di\Config;
use PHPUnit_Framework_TestCase as TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var array
     */
    private $fixture;

    /**
     * {@inheritDoc}
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fixture = include __DIR__ . '/_files/sample-config.php';
    }

    public function testGetConfiguredTypeName()
    {
        $config = new Config($this->fixture);
        $this->assertEquals(['Foo', 'Bar'], $config->getConfiguredTypeNames());
    }

    public function testIsAlias()
    {
        $config = new Config($this->fixture);
        $this->assertTrue($config->isAlias('Bar'));
        $this->assertFalse($config->isAlias('Foo'));
        $this->assertFalse($config->isAlias('DoesNotExist'));
    }

    public function testGetClassForAlias()
    {
        $config = new Config($this->fixture);
        $this->assertEquals('Foo', $config->getClassForAlias('Bar'));
        $this->assertNull($config->getClassForAlias('Foo'));
        $this->assertNull($config->getClassForAlias('DoesNotExist'));
    }

    public function testGetParameters()
    {
        $config = new Config($this->fixture);
        $this->assertEquals(['a' => '*'], $config->getParameters('Foo'));
        $this->assertEquals([], $config->getParameters('Bar'));
        $this->assertEquals([], $config->getParameters('A'));
        $this->assertEquals([], $config->getParameters('B'));
    }

    public function testGetTypePreference()
    {
        $config = new Config($this->fixture);
        $this->assertEquals('GlobalA', $config->getTypePreference('A'));
        $this->assertEquals('GlobalB', $config->getTypePreference('B'));
        $this->assertNull($config->getTypePreference('NotDefined'));

        $this->assertEquals('LocalA', $config->getTypePreference('A', 'Foo'));
        $this->assertNull($config->getTypePreference('B', 'Foo'));
        $this->assertNull($config->getTypePreference('NotDefined', 'Foo'));

        $this->assertEquals('LocalB', $config->getTypePreference('B', 'Bar'));
        $this->assertNull($config->getTypePreference('A', 'Bar'));
        $this->assertNull($config->getTypePreference('NotDefined', 'Bar'));

        $this->assertNull($config->getTypePreference('A', 'NotDefinedType'));
        $this->assertNull($config->getTypePreference('B', 'NotDefinedType'));
        $this->assertNull($config->getTypePreference('NotDefined', 'NotDefinedType'));
    }
}
