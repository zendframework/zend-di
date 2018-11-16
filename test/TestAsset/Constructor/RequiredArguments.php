<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di\TestAsset\Constructor;

use ArrayAccess;

class RequiredArguments
{
    public function __construct(NoConstructor $objectDep, ArrayAccess $internalClassDep, $anyDep)
    {
    }
}
