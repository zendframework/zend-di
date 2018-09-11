<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Resolver;

use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\TestCase;
use Zend\Di\Resolver\AbstractInjection;
use Zend\Di\Resolver\InjectionInterface;

class AbstractInjectionTest extends TestCase
{
    public function testUsageIsDeprecated()
    {
        $this->expectException(Deprecated::class);
        $this->expectExceptionMessage(sprintf(
            '%s is deprecated, please migrate to %s',
            AbstractInjection::class,
            InjectionInterface::class
        ));

        new class() extends AbstractInjection
        {
            public function export(): string
            {
            }

            public function isExportable(): bool
            {
            }
        };
    }
}
