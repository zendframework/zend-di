<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Definition;

/**
 * Parameter definition
 */
interface ParameterInterface
{
    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return int
     */
    public function getPosition() : int;

    /**
     * @return string|null
     */
    public function getType() : ?string;

    /**
     * @return mixed
     */
    public function getDefault();

    /**
     * @return bool
     */
    public function isRequired() : bool;

    /**
     * @return bool
     */
    public function isBuiltin() : bool;
}
