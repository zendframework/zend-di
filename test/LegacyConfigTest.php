<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Deprecated as DeprecatedError;
use Zend\Di\Exception;
use Zend\Di\LegacyConfig;
use ArrayIterator;
use GlobIterator;
use stdClass;

/**
 * @coversDefaultClass Zend\Di\LegacyConfig
 */
class LegacyConfigTest extends TestCase
{
    public function provideMigrationConfigFixtures()
    {
        $iterator = new GlobIterator(__DIR__ . '/_files/legacy-configs/*.php');
        $values = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $key = $file->getBasename('.php');
            $data = include $file->getPathname();

            $values[$key] = [
                $data['config'],
                $data['expected'],
            ];
        }

        return $values;
    }

    /**
     * @dataProvider provideMigrationConfigFixtures
     */
    public function testLegacyConfigMigration(array $config, array $expected)
    {
        $instance = new LegacyConfig($config);
        $this->assertEquals($expected, $instance->toArray());
    }

    public function testFQParamNamesTriggerDeprecated()
    {
        $this->expectException(DeprecatedError::class);

        new LegacyConfig([
            'instance' => [
                'FooClass' => [
                    'parameters' => [
                        'BarClass:__construct:0' => 'Value for fq param name',
                    ],
                ],
            ],
        ]);
    }

    public function testConstructWithTraversable()
    {
        $spec = include __DIR__ . '/_files/legacy-configs/common.php';
        $config = new ArrayIterator($spec['config']);
        $instance = new LegacyConfig($config);

        $this->assertEquals($spec['expected'], $instance->toArray());
    }

    public function testConstructWithInvalidConfigThrowsException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new LegacyConfig(new stdClass());
    }
}
