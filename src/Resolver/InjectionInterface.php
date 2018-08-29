<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Resolver;

use Psr\Container\ContainerInterface;

/**
 * Defines the injection to perform for a parameter
 */
interface InjectionInterface
{
    /**
     * @return mixed The resulting injection value
     */
    public function toValue(ContainerInterface $container);

    /**
     * @return string
     */
    public function export() : string;

    /**
     * @return bool
     */
    public function isExportable() : bool;
}
