<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\TestAsset;

class Parameters
{
    public function general($a, B $b, $c = 'something')
    {}

    public function typehintRequired(A $foo)
    {}

    public function typelessRequired($bar)
    {}

    public function typehintOptional(A $fooOpt = null)
    {}

    public function typelessOptional($flag = false)
    {}

}
