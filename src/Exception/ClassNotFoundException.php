<?php

declare(strict_types=1);

/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Exception;

use DomainException;
use Throwable;

class ClassNotFoundException extends DomainException implements ExceptionInterface
{
    public function __construct(string $classname, ?int $code = null, ?Throwable $previous = null)
    {
        parent::__construct("The class '$classname' does not exist.", $code ?? 0, $previous);
    }
}
