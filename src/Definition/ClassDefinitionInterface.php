<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Definition;

use ReflectionClass;

interface ClassDefinitionInterface
{
    /**
     * @return ReflectionClass
     */
    public function getReflection() : ReflectionClass;

    /**
     * @return string[]
     */
    public function getSupertypes() : array;

    /**
     * @return string[]
     */
    public function getInterfaces() : array;

    /**
     * @return ParameterInterface[]
     */
    public function getParameters() : array;
}
