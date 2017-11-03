<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\CodeGenerator;

use Zend\Di\CodeGenerator\AutoloadGenerator;

/**
 * AutoloadGenerator test case.
 */
class AutoloadGeneratorTest extends \PHPUnit_Framework_TestCase
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
