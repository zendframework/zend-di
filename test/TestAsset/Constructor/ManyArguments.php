<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\TestAsset\Constructor;

use function array_filter;
use function compact;

class ManyArguments
{
    public $result;

    public function __construct(
        $a = null,
        $b = null,
        $c = null,
        $d = null,
        $e = null,
        $f = null
    ) {
        $this->result = array_filter(compact('a', 'b', 'c', 'd', 'e', 'f'), function ($value) {
            return $value !== null;
        });
    }
}
