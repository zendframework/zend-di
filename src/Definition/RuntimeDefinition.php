<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Definition;

use Zend\Di\Exception;

/**
 * Class definitions based on runtime reflection
 */
class RuntimeDefinition implements DefinitionInterface
{
    /**
     * @var \Zend\Di\Definition\Reflection\ClassDefinition[string]
     */
    private $definition = [];

    /**
     * @var bool[string]
     */
    private $explicitClasses = null;

    /**
     * @param string[]|null $explicitClasses
     */
    public function __construct(array $explicitClasses = null)
    {
        if ($explicitClasses) {
            $this->setExplicitClasses($explicitClasses);
        }
    }

    /**
     * Set explicit class names
     *
     * @see     addExplicitClass()
     * @param   string[]    $explicitClasses        An array of class names
     * @throws  \Zend\Di\Exception\ClassNotFoundException
     * @return  self
     */
    public function setExplicitClasses(array $explicitClasses)
    {
        $this->explicitClasses = [];

        foreach ($explicitClasses as $class) {
            $this->addExplicitClass($class);
        }

        return $this;
    }

    /**
     * Add class name explicitly
     *
     * Adding classes this way will cause the defintion to report them when getClasses()
     * is called, even when they're not yet loaded.
     *
     * @param   string  $class
     * @throws  \Zend\Di\Exception\ClassNotFoundException
     * @return  self
     */
    public function addExplicitClass($class)
    {
        if (!class_exists($class)) {
            throw new Exception\ClassNotFoundException($class);
        }

        if (!$this->explicitClasses) {
            $this->explicitClasses = [];
        }

        $this->explicitClasses[$class] = true;
        return $this;
    }

    /**
     * @param   string  $class  The class name to load
     * @throws  \Zend\Di\Exception\ClassNotFoundException
     */
    private function loadClass($class)
    {
        if (!$this->hasClass($class)) {
            throw new Exception\ClassNotFoundException($class);
        }

        $this->definition[$class] = new Reflection\ClassDefinition($class);
    }

    /**
     * @return string[]
     */
    public function getClasses()
    {
        if (!$this->explicitClasses) {
            return array_keys($this->definition);
        }

        return array_keys(array_merge($this->definition, $this->explicitClasses));
    }

    /**
     * @return bool
     */
    public function hasClass($class)
    {
        return class_exists($class);
    }

    /**
     * @param   string  $class
     * @return  \Zend\Di\Definition\Reflection\ClassDefinition
     * @throws  \Zend\Di\Exception\ClassNotFoundException
     */
    public function getClassDefinition($class)
    {
        if (!isset($this->definition[$class])) {
            $this->loadClass($class);
        }

        return $this->definition[$class];
    }
}
