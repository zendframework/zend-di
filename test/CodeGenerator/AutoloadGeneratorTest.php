<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\CodeGenerator;

use Zend\Di\CodeGenerator\AutoloadGenerator;
use PHPUnit\Framework\TestCase;

/**
 * AutoloadGenerator test case.
 */
class AutoloadGeneratorTest extends TestCase
{
    const DEFAULT_NAMESPACE = 'ZendTest\Di\Generated';

    use GeneratorTestTrait;

    public function testGenerateCreatesFiles()
    {
        $generator = new AutoloadGenerator('ZendTest\Di\Generated');
        $generator->setOutputDirectory($this->dir);
        $classmap = [
            'FooClass' => 'FooClass.php',
            'Bar\\Class' => 'Bar/Class.php'
        ];

        $generator->generate($classmap);
        $this->assertFileExists($this->dir . '/Autoloader.php');
        $this->assertFileExists($this->dir . '/autoload.php');
    }
}
