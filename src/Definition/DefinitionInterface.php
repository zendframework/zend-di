<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
    public function getClasses();

    /**
     * Whether a class exists in this definition
     *
     * @param  string $class
     * @return bool
     */
    public function hasClass($class);

    /**
     * @param  string   $class
     * @throws \Zend\Di\Exception\ClassNotFoundException
     * @return ClassDefinitionInterface
     */
    public function getClassDefinition($class);
}
