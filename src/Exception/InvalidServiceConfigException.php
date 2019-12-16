<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Exception;

use Psr\Container\ContainerExceptionInterface;

class InvalidServiceConfigException extends LogicException implements ContainerExceptionInterface
{
}
