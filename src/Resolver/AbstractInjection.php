<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Resolver;

abstract class AbstractInjection
{
    /**
     * @var string
     */
    private $parameterName;

    /**
     * @param string $name
     * @return self
     */
    public function setParameterName(string $name) : self
    {
        $this->parameterName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getParameterName() : string
    {
        return $this->parameterName;
    }

    /**
     * @return string
     */
    abstract public function export() : string;

    /**
     * @return bool
     */
    abstract public function isExportable() : bool;
}
