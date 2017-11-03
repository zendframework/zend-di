<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Definition\Reflection;

use Zend\Di\Definition\ParameterInterface;
use Zend\Di\Definition\ClassDefinitionInterface;


class ClassDefinition implements ClassDefinitionInterface
{
    /**
     * @var \ReflectionClass
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
     * @param string|\ReflectionClass $class
     */
    public function __construct($class)
    {
        if (!$class instanceof \ReflectionClass) {
            $class = new \ReflectionClass($class);
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
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * @return string[]
     */
    public function getSupertypes()
    {
        if ($this->supertypes === null) {
            $this->reflectSupertypes();
        }

        return $this->supertypes;
    }

    /**
     * @return string[]
     */
    public function getInterfaces()
    {
        return $this->reflection->getInterfaceNames();
    }

    /**
     * @return void
     */
    private function reflectParamters()
    {
        $this->parameters = [];

        if (!$this->reflection->hasMethod('__construct')) {
            return;
        }

        $method = $this->reflection->getMethod('__construct');
        $class = (version_compare(PHP_VERSION, '7.0', '<'))? LegacyParameter::class : Parameter::class;

        /** @var \ReflectionParameter $parameterReflection */
        foreach ($method->getParameters() as $parameterReflection) {
            $parameter = new $class($parameterReflection);
            $this->parameters[$parameter->getName()] = $parameter;
        }

        uasort($this->parameters, function (ParameterInterface $a, ParameterInterface $b) {
            return $a->getPosition() - $b->getPosition();
        });
    }

    /**
     * @return Parameter[]
     */
    public function getParameters()
    {
        if ($this->parameters === null) {
            $this->reflectParamters();
        }

        return $this->parameters;
    }
}
