<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\CodeGenerator;

use PHPUnit\Framework\TestCase;
use Zend\Di\CodeGenerator\AutoloadGenerator;

/**
 * AutoloadGenerator test case.
 */
class AutoloadGeneratorTest extends TestCase
{
    const DEFAULT_NAMESPACE = 'ZendTest\Di\Generated';

    use GeneratorTestTrait;

    public function testGenerateCreatesFiles()
    {
        $classmap = [];
        $generator = new AutoloadGenerator(self::DEFAULT_NAMESPACE);
        $generator->setOutputDirectory($this->dir);
        $generator->generate($classmap);

        $this->assertFileExists($this->dir . '/Autoloader.php');
        $this->assertFileExists($this->dir . '/autoload.php');
    }

    public function testGeneratedAutoloaderClass()
    {
        $generator = new AutoloadGenerator(self::DEFAULT_NAMESPACE);
        $generator->setOutputDirectory($this->dir);
        $classmap = [
            'FooClass' => 'FooClass.php',
            'Bar\\Class' => 'Bar/Class.php'
        ];

        $generator->generate($classmap);

        self::assertFileEquals(
            __DIR__ . '/../_files/expected-codegen-results/autoloader-class.php',
            $this->dir . '/Autoloader.php'
        );
    }

    public function testGeneratedAutoloadFile()
    {
        $generator = new AutoloadGenerator(self::DEFAULT_NAMESPACE);
        $generator->setOutputDirectory($this->dir);
        $classmap = [
            'FooClass' => 'FooClass.php',
            'Bar\\Class' => 'Bar/Class.php'
        ];

        $generator->generate($classmap);

        self::assertFileEquals(
            __DIR__ . '/../_files/expected-codegen-results/autoload-file.php',
            $this->dir . '/autoload.php'
        );
    }
}
