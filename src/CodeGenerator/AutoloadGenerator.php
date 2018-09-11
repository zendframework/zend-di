<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\CodeGenerator;

use SplFileObject;
use Throwable;
use Zend\Di\Exception\GenerateCodeException;

use function array_keys;
use function array_map;
use function file_get_contents;
use function implode;
use function str_repeat;
use function strtr;
use function var_export;

class AutoloadGenerator
{
    use GeneratorTrait;

    private const CLASS_TEMPLATE = __DIR__ . '/../../templates/autoloader-class.template';
    private const FILE_TEMPLATE = __DIR__ . '/../../templates/autoloader-file.template';

    /**
     * @var string
     */
    private $namespace;

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    private function writeFile(string $filename, string $code) : void
    {
        try {
            $file = new SplFileObject($filename, 'w');
            $file->fwrite($code);
        } catch (Throwable $e) {
            throw new GenerateCodeException(sprintf('Failed to write output file "%s"', $filename), 0, $e);
        }
    }

    private function buildFromTemplate(string $templateFile, string $outputFile, array $replacements) : void
    {
        $template = file_get_contents($templateFile);
        $code = strtr($template, $replacements);
        $outputFile = $this->outputDirectory . '/' . $outputFile;

        $this->writeFile($outputFile, $code);
    }

    private function generateClassmapCode(array &$classmap) : string
    {
        $lines = array_map(
            function (string $class, string $file): string {
                return var_export($class, true) . ' => ' . var_export($file, true) . ',';
            },
            array_keys($classmap),
            $classmap
        );

        $indent = str_repeat(' ', 8);
        return implode("\n$indent", $lines);
    }

    private function generateAutoloaderClass(array &$classmap) : void
    {
        $replacements = [
            '%namespace%' => $this->namespace ? sprintf("namespace %s;\n", $this->namespace) : '',
            '%classmap%' => $this->generateClassmapCode($classmap),
        ];

        $this->buildFromTemplate(self::CLASS_TEMPLATE, 'Autoloader.php', $replacements);
    }

    private function generateAutoloadFile() : void
    {
        $replacements = [
            '%namespace%' => $this->namespace ? sprintf("namespace %s;\n", $this->namespace) : '',
        ];

        $this->buildFromTemplate(self::FILE_TEMPLATE, 'autoload.php', $replacements);
    }

    /**
     * @param string[] $classmap
     */
    public function generate(array $classmap) : void
    {
        $this->ensureOutputDirectory();
        $this->generateAutoloaderClass($classmap);
        $this->generateAutoloadFile();
    }
}
