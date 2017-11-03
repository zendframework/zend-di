<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Definition\Reflection;

use Zend\Di\Definition\ParameterInterface;


/**
 * This class specifies a method parameter for the di definition
 */
class Parameter implements ParameterInterface
{
    /**
     * @var \ReflectionParameter
     */
    protected $reflection;

    /**
     * @param \ReflectionParameter $reflection
     */
    public function __construct(\ReflectionParameter $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\Definition\ParameterInterface::getDefault()
     */
    public function getDefault()
    {
        return $this->reflection->getDefaultValue();
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\Definition\ParameterInterface::getName()
     */
    public function getName()
    {
        return $this->reflection->getName();
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\Definition\ParameterInterface::getPosition()
     */
    public function getPosition()
    {
        return $this->reflection->getPosition();
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\Definition\ParameterInterface::getType()
     */
    public function getType()
    {
        if ($this->reflection->hasType()) {
            return (string)$this->reflection->getType();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\Definition\ParameterInterface::isRequired()
     */
    public function isRequired()
    {
        return !$this->reflection->isOptional();
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\Definition\ParameterInterface::isScalar()
     */
    public function isBuiltin()
    {
        if ($this->reflection->hasType()) {
            return $this->reflection->getType()->isBuiltin();
        }

        return false;
    }
}
