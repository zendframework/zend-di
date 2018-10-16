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
        $this->writeFile(
            sprintf('%s/%s', $this->outputDirectory, $outputFile),
            strtr(
                file_get_contents($templateFile),
                $replacements
            )
        );
    }

    private function generateClassmapCode(array &$classmap) : string
    {
        $lines = array_map(
            function (string $class, string $file): string {
                return sprintf(
                    '%s => %s,',
                    var_export($class, true),
                    var_export($file, true)
                );
            },
            array_keys($classmap),
            $classmap
        );

        $indentation = sprintf("\n%s", str_repeat(' ', 8));
        return implode($indentation, $lines);
    }

    private function generateAutoloaderClass(array &$classmap) : void
    {
        $this->buildFromTemplate(self::CLASS_TEMPLATE, 'Autoloader.php', [
            '%namespace%' => $this->namespace ? sprintf("namespace %s;\n", $this->namespace) : '',
            '%classmap%' => $this->generateClassmapCode($classmap),
        ]);
    }

    private function generateAutoloadFile() : void
    {
        $this->buildFromTemplate(self::FILE_TEMPLATE, 'autoload.php', [
            '%namespace%' => $this->namespace ? sprintf("namespace %s;\n", $this->namespace) : '',
        ]);
    }

    /**
     * @param string[] $classmap
     */
    public function generate(array &$classmap) : void
    {
        $this->ensureOutputDirectory();
        $this->generateAutoloaderClass($classmap);
        $this->generateAutoloadFile();
    }
}
