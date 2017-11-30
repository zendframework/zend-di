<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\TestAsset\DependencyTree;

class Complex
{
    /**
     * @var Level1
     */
    public $result;

    /**
     * @var AdditionalLevel1
     */
    public $result2;

    public function __construct(Level1 $dep, AdditionalLevel1 $dep2)
    {
        $this->result = $dep;
        $this->result2 = $dep2;
    }
}
