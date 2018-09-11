<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Resolver;

use Psr\Container\ContainerInterface;
use Zend\Di\Exception\LogicException;

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
     * Export the injection to PHP code
     *
     * This will be used by code generators to provide AoT factories
     *
     * @throws LogicException When the injection is not exportable
     */
    public function export() : string;

    /**
     * Whether this injection can be exported as code or not
     *
     * This must determinate if an export of this injection as PHP code
     * is possible or not.
     *
     * When this method returns false, a call to `export()` should throw a
     * `Zend\Di\Exception\LogicException`
     */
    public function isExportable() : bool;
}
