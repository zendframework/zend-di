<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\TestAsset\DependencyTree;

class Simple
{
    /**
     * @var Level1
     */
    public $result;

    public function __construct(Level1 $dep)
    {
        $this->result = $dep;
    }
}
