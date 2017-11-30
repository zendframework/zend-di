<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Definition\Reflection;

use ReflectionClass;
use Zend\Di\Definition\ClassDefinitionInterface;
use Zend\Di\Definition\ParameterInterface;

class ClassDefinition implements ClassDefinitionInterface
{
    /**
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * @var Parameter[]
     */
    private $parameters = null;

    /**
     * @var string[]
     */
    private $supertypes = null;

    /**
     * @param string|ReflectionClass $class
     */
    public function __construct($class)
    {
        if (! $class instanceof ReflectionClass) {
            $class = new ReflectionClass($class);
        }

        $this->reflection = $class;
    }

    /**
     * @return void
     */
    private function reflectSupertypes()
    {
        $this->supertypes = [];
        $class = $this->reflection;

        while ($class = $class->getParentClass()) {
            $this->supertypes[] = $class->name;
        }
    }

    /**
     * @return ReflectionClass
     */
    public function getReflection() : ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return string[]
     */
    public function getSupertypes() : array
    {
        if ($this->supertypes === null) {
            $this->reflectSupertypes();
        }

        return $this->supertypes;
    }

    /**
     * @return string[]
     */
    public function getInterfaces() : array
    {
        return $this->reflection->getInterfaceNames();
    }

    /**
     * @return void
     */
    private function reflectParameters()
    {
        $this->parameters = [];

        if (! $this->reflection->hasMethod('__construct')) {
            return;
        }

        $method = $this->reflection->getMethod('__construct');

        /** @var \ReflectionParameter $parameterReflection */
        foreach ($method->getParameters() as $parameterReflection) {
            $parameter = new Parameter($parameterReflection);
            $this->parameters[$parameter->getName()] = $parameter;
        }

        uasort($this->parameters, function (ParameterInterface $a, ParameterInterface $b) {
            return $a->getPosition() - $b->getPosition();
        });
    }

    /**
     * @return Parameter[]
     */
    public function getParameters() : array
    {
        if ($this->parameters === null) {
            $this->reflectParameters();
        }

        return $this->parameters;
    }
}
