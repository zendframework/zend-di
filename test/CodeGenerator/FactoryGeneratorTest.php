<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\CodeGenerator;

use Zend\Di\CodeGenerator\FactoryGenerator;
use Zend\Di\Config;
use Zend\Di\Resolver\DependencyResolver;
use Zend\Di\Definition\RuntimeDefinition;
use ZendTest\Di\TestAsset;

/**
 * FactoryGenerator test case.
 */
class FactoryGeneratorTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_NAMESPACE = 'ZendTest\Di\Generated';

    use GeneratorTestTrait;

    public function testGenerateCreatesFiles()
    {
        $config = new Config();
        $resolver = new DependencyResolver(new RuntimeDefinition(), $config);
        $generator = new FactoryGenerator($config, $resolver, self::DEFAULT_NAMESPACE);

        $generator->setOutputDirectory($this->dir . '/Factory');
        $generator->generate(TestAsset\RequiresA::class);

        $this->assertFileExists($this->dir . '/Factory/ZendTest/Di/TestAsset/RequiresAFactory.php');
    }

    public function testGenerateBuildsUpClassMap()
    {
        $config = new Config();
        $resolver = new DependencyResolver(new RuntimeDefinition(), $config);
        $generator = new FactoryGenerator($config, $resolver, self::DEFAULT_NAMESPACE);

        $generator->setOutputDirectory($this->dir . '/FactoryMultiple');

        $f1 = $generator->generate(TestAsset\RequiresA::class);
        $f2 = $generator->generate(TestAsset\Constructor\EmptyConstructor::class);

        $expected = [
            $f1 => str_replace('\\', '/', TestAsset\RequiresA::class) . 'Factory.php',
            $f2 => str_replace('\\', '/', TestAsset\Constructor\EmptyConstructor::class) . 'Factory.php',
        ];

        $this->assertEquals($expected, $generator->getClassmap());
    }
}
