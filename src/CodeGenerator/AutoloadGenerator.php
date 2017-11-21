<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\CodeGenerator;

use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\PropertyValueGenerator;

class AutoloadGenerator
{
    use GeneratorTrait;

    private $namespace;

    /**
     * @param string $namespace
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    private function generateAutoloaderClass(array &$classmap)
    {
        $class = new ClassGenerator('Autoloader');
        $classmapValue = new PropertyValueGenerator(
            $classmap,
            PropertyValueGenerator::TYPE_ARRAY_SHORT,
            PropertyValueGenerator::OUTPUT_MULTIPLE_LINE
        );

        $registerCode = 'if (!$this->registered) {'.PHP_EOL
            . '    spl_autoload_register($this);'.PHP_EOL
            . '    $this->registered = true;'.PHP_EOL
            . '}'.PHP_EOL
            . 'return $this;';

        $unregisterCode = 'if ($this->registered) {'.PHP_EOL
            . '    spl_autoload_unregister($this);'.PHP_EOL
            . '    $this->registered = false;'.PHP_EOL
            . '}'.PHP_EOL
            . 'return $this;';

        $loadCode = 'if (isset($this->classmap[$class])) {'.PHP_EOL
            . '    include __DIR__ . \'/\' . $this->classmap[$class];'.PHP_EOL
            . '}';

        $class
            ->addProperty('registered', false, PropertyGenerator::FLAG_PRIVATE)
            ->addProperty('classmap', $classmapValue, PropertyGenerator::FLAG_PRIVATE)
            ->addMethod('register', [], MethodGenerator::FLAG_PUBLIC, $registerCode)
            ->addMethod('unregister', [], MethodGenerator::FLAG_PUBLIC, $unregisterCode)
            ->addMethod('load', ['class'], MethodGenerator::FLAG_PUBLIC, $loadCode)
            ->addMethod('__invoke', ['class'], MethodGenerator::FLAG_PUBLIC, '$this->load($class);');

        $file = new FileGenerator();
        $file
            ->setDocBlock(new DocBlockGenerator('Generated autoloader for Zend\Di'))
            ->setNamespace($this->namespace)
            ->setClass($class)
            ->setFilename($this->outputDirectory . '/Autoloader.php');

        $file->write();
    }

    /**
     * @param array $classmap
     */
    public function generate(array &$classmap)
    {
        $this->ensureOutputDirectory();
        $this->generateAutoloaderClass($classmap);

        $code = "require_once __DIR__ . '/Autoloader.php';\n"
            . 'return (new Autoloader())->register();';

        $file = new FileGenerator();
        $file
            ->setDocBlock(new DocBlockGenerator('Generated autoload file for Zend\Di'))
            ->setNamespace($this->namespace)
            ->setBody($code)
            ->setFilename($this->outputDirectory.'/autoload.php')
            ->write();
    }
}
