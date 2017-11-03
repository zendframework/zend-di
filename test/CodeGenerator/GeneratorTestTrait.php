<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\CodeGenerator;

trait GeneratorTestTrait
{
    private $dir;

    /**
     * @param string $dir
     * @throws \RuntimeException
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $dirIterator = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);
        $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $result = rmdir($file->getPathname());
            } else {
                $result = unlink($file->getPathname());
            }

            if (!$result) {
                throw new \RuntimeException('Failed to remove "' . $file->getPathname() . '"');
            }
        }

        if (!rmdir($dir)) {
            throw new \RuntimeException('Failed to remove "' . $file->getPathname() . '"');
        }
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dir = __DIR__ . '/_result';

        $this->removeDirectory($this->dir);
        mkdir($this->dir, 0777);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->removeDirectory($this->dir);
        parent::tearDown();
    }
}
