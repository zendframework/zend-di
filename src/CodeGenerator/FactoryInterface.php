<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\CodeGenerator;

use Psr\Container\ContainerInterface;

interface FactoryInterface
{
    /**
     * Create an instance
     *
     * @param array $options
     * @return object
     */
    public function create(ContainerInterface $container, array $options);
}
