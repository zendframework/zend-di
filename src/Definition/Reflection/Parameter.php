<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Definition\Reflection;

use ReflectionParameter;
use Zend\Di\Definition\ParameterInterface;

/**
 * This class specifies a method parameter for the di definition
 */
class Parameter implements ParameterInterface
{
    /**
     * @var ReflectionParameter
     */
    protected $reflection;

    /**
     * @param ReflectionParameter $reflection
     */
    public function __construct(ReflectionParameter $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * {@inheritDoc}
     * @see ParameterInterface::getDefault()
     */
    public function getDefault()
    {
        return $this->reflection->getDefaultValue();
    }

    /**
     * {@inheritDoc}
     * @see ParameterInterface::getName()
     */
    public function getName() : string
    {
        return $this->reflection->getName();
    }

    /**
     * {@inheritDoc}
     * @see ParameterInterface::getPosition()
     */
    public function getPosition() : int
    {
        return $this->reflection->getPosition();
    }

    /**
     * {@inheritDoc}
     * @see ParameterInterface::getType()
     */
    public function getType() : ?string
    {
        if ($this->reflection->hasType()) {
            return (string)$this->reflection->getType();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see ParameterInterface::isRequired()
     */
    public function isRequired() : bool
    {
        return ! $this->reflection->isOptional();
    }

    /**
     * {@inheritDoc}
     * @see ParameterInterface::isScalar()
     */
    public function isBuiltin() : bool
    {
        if ($this->reflection->hasType()) {
            return $this->reflection->getType()->isBuiltin();
        }

        return false;
    }
}
