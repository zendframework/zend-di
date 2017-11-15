<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\TestAsset\Constructor;

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
        $this->result = array_filter(compact('a', 'b', 'c', 'd', 'e', 'f'), function($value) {
            return ($value !== null);
        });
    }
}
