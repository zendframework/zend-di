<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Container;

use Psr\Container\ContainerInterface;

class GeneratorFactory
{
    public function create(ContainerInterface $container)
    {
        // TODO: Implement the factory
    }

    public function __invoke(ContainerInterface $container)
    {
        return $this->create($container);
    }
}
