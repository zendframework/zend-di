<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\TestAsset\CodeGenerator;

use Psr\Container\ContainerInterface;
use stdClass;
use Zend\Di\CodeGenerator\FactoryInterface;

class StdClassFactory implements FactoryInterface
{
    public function create(ContainerInterface $container, array $options)
    {
        return new stdClass();
    }
}
