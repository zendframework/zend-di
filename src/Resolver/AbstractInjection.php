<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
    public function setParameterName($name)
    {
        $this->parameterName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getParameterName()
    {
        return $this->parameterName;
    }

    /**
     * @return string
     */
    abstract public function export();

    /**
     * @return bool
     */
    abstract public function isExportable();
}
