<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Definition;

/**
 * Interface for class definitions
 */
interface DefinitionInterface
{
    /**
     * All class names in this definition
     *
     * @return string[]
     */
    public function getClasses() : array;

    /**
     * Whether a class exists in this definition
     *
     * @param  string $class
     * @return bool
     */
    public function hasClass(string $class) : bool;

    /**
     * @param string $class
     * @throws \Zend\Di\Exception\ClassNotFoundException
     * @return ClassDefinitionInterface
     */
    public function getClassDefinition(string $class) : ClassDefinitionInterface;
}
