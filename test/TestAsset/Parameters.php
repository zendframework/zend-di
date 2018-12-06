<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\TestAsset;

class Parameters
{
    public function general($a, B $b, $c = 'something')
    {
    }

    public function typehintRequired(A $foo)
    {
    }

    public function typelessRequired($bar)
    {
    }

    public function typehintOptional(?A $fooOpt = null)
    {
    }

    public function typelessOptional($flag = false)
    {
    }
}
