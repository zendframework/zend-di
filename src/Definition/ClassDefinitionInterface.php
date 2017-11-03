<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Definition;

interface ClassDefinitionInterface
{
    /**
     * @return \ReflectionClass
     */
    public function getReflection();

    /**
     * @return string[]
     */
    public function getSupertypes();

    /**
     * @return string[]
     */
    public function getInterfaces();

    /**
     * @return ParameterInterface[]
     */
    public function getParameters();
}
