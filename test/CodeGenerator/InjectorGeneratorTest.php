<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\CodeGenerator;

use PHPUnit\Framework\TestCase;
use Zend\Di\CodeGenerator\InjectorGenerator;
use Zend\Di\Config;
use Zend\Di\Definition\RuntimeDefinition;
use Zend\Di\Resolver\DependencyResolver;
use ZendTest\Di\TestAsset;
use Zend\Di\InjectorInterface;

/**
 * FactoryGenerator test case.
 */
class InjectorGeneratorTest extends TestCase
{
    const DEFAULT_NAMESPACE = 'ZendTest\Di\Generated';

    use GeneratorTestTrait;

    public function testGenerateCreatesFiles() : void
    {
        $config = new Config();
        $resolver = new DependencyResolver(new RuntimeDefinition(), $config);
        $generator = new InjectorGenerator($config, $resolver, self::DEFAULT_NAMESPACE);

        $generator->setOutputDirectory($this->dir);
        $generator->generate([
            TestAsset\RequiresA::class
        ]);

        $this->assertFileExists($this->dir . '/Factory/ZendTest/Di/TestAsset/RequiresAFactory.php');
        $this->assertFileExists($this->dir . '/GeneratedInjector.php');
        $this->assertFileExists($this->dir . '/factories.php');
        $this->assertFileExists($this->dir . '/autoload.php');
    }

    public function testGeneratedInjectorIsValidCode() : void
    {
        // The namespace must be unique, Since we will attempt to load the
        // generated class
        $namespace = self::DEFAULT_NAMESPACE . uniqid();
        $config = new Config();
        $resolver = new DependencyResolver(new RuntimeDefinition(), $config);
        $generator = new InjectorGenerator($config, $resolver, $namespace);
        $class = $namespace . '\\GeneratedInjector';

        $generator->setOutputDirectory($this->dir);
        $generator->generate([]);

        $this->assertFalse(class_exists($class, false));
        include $this->dir . '/GeneratedInjector.php';
        $this->assertTrue(class_exists($class, false));
    }

    public function testSetCustomNamespace() : void
    {
        $expected = self::DEFAULT_NAMESPACE . uniqid();
        $config = new Config();
        $resolver = new DependencyResolver(new RuntimeDefinition(), $config);
        $generator = new InjectorGenerator($config, $resolver, $expected);

        $this->assertEquals($expected, $generator->getNamespace());
    }
}
