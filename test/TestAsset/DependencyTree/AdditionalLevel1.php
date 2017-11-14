<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Di\TestAsset\DependencyTree;

class AdditionalLevel1
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