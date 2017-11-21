<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Definition;

use Zend\Di\Exception;

/**
 * Class definitions based on runtime reflection
 */
class RuntimeDefinition implements DefinitionInterface
{
    /**
     * @var Reflection\ClassDefinition[string]
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
     * @see addExplicitClass()
     * @param string[] $explicitClasses An array of class names
     * @throws Exception\ClassNotFoundException
     * @return self
     */
    public function setExplicitClasses(array $explicitClasses) : self
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
     * @param string $class
     * @throws Exception\ClassNotFoundException
     * @return self
     */
    public function addExplicitClass(string $class) : self
    {
        if (! class_exists($class)) {
            throw new Exception\ClassNotFoundException($class);
        }

        if (! $this->explicitClasses) {
            $this->explicitClasses = [];
        }

        $this->explicitClasses[$class] = true;
        return $this;
    }

    /**
     * @param string $class The class name to load
     * @throws Exception\ClassNotFoundException
     */
    private function loadClass(string $class)
    {
        if (! $this->hasClass($class)) {
            throw new Exception\ClassNotFoundException($class);
        }

        $this->definition[$class] = new Reflection\ClassDefinition($class);
    }

    /**
     * @return string[]
     */
    public function getClasses() : array
    {
        if (! $this->explicitClasses) {
            return array_keys($this->definition);
        }

        return array_keys(array_merge($this->definition, $this->explicitClasses));
    }

    /**
     * @return bool
     */
    public function hasClass(string $class) : bool
    {
        return class_exists($class);
    }

    /**
     * @param string $class
     * @return Reflection\ClassDefinition
     * @throws Exception\ClassNotFoundException
     */
    public function getClassDefinition(string $class) : ClassDefinitionInterface
    {
        if (! isset($this->definition[$class])) {
            $this->loadClass($class);
        }

        return $this->definition[$class];
    }
}
