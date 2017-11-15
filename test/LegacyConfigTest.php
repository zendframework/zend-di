<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Deprecated as DeprecatedError;
use Zend\Di\LegacyConfig;
use Zend\Di\Exception;

class LegacyConfigTest extends TestCase
{
    public function provideMigrationConfigFixtures()
    {
        $iterator = new \GlobIterator(__DIR__ . '/_files/legacy-configs/*.php');

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $data = include $file->getPathname();
            yield [
                $data['config'],
                $data['expected']
            ];
        }
    }

    /**
     * @dataProvider provideMigrationConfigFixtures
     */
    public function testLegacyConfigMigration(array $config, array $expected)
    {
        $instance = new LegacyConfig($config);
        $this->assertEquals($expected, $instance->toArray());
    }

    public function testFQParamNamesTriggerDeprectade()
    {
        $this->expectException(DeprecatedError::class);

        new LegacyConfig([
            'instance' => [
                'FooClass' => [
                    'parameters' => [
                        'BarClass:__construct:0' => 'Value for fq param name'
                    ]
                ]
            ]
        ]);
    }

    public function testConstructWithTraversable()
    {
        $spec = include __DIR__ . '/_files/legacy-configs/a.php';
        $config = new \ArrayIterator($spec['config']);
        $instance = new LegacyConfig($config);

        $this->assertEquals($spec['expected'], $instance->toArray());
    }

    public function testConstructWithInvalidConfigThrowsException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new LegacyConfig(new \stdClass());
    }
}
