<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Exception;

use DomainException;

class ClassNotFoundException extends DomainException implements ExceptionInterface
{
    /**
     * @param   string          $classname
     * @param   int             $code
     * @param   \Throwable|null $previous
     */
    public function __construct($classname, $code = null, $previous = null)
    {
        parent::__construct("The class '$classname' does not exist.", $code, $previous);
    }
}
