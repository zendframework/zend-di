<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\TestAsset\DependencyTree;

class Level1
{
    /**
     * @var Level2
     */
    public $result;

    public function __construct(Level2 $dep)
    {
        $this->result = $dep;
    }
}
