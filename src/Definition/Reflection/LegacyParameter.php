<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Definition\Reflection;

/**
 * This class specifies a method parameter for the di definition
 */
class LegacyParameter extends Parameter
{
    /**
     * {@inheritDoc}
     * @see \Zend\Di\Definition\Reflection\Parameter::getType()
     */
    public function getType()
    {
        if ($this->reflection->isArray()) {
            return 'array';
        } elseif ($this->reflection->isCallable()) {
            return 'callable';
        }

        $class = $this->reflection->getClass();
        return $class? $class->getName() : null;
    }
    /**
     * {@inheritDoc}
     * @see \Zend\Di\Definition\ParameterInterface::isScalar()
     */
    public function isBuiltin()
    {
        if ($this->reflection->isArray() || $this->reflection->isCallable()) {
            return true;
        }

        return false;
    }
}
