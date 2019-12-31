<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

use Psr\Container\ContainerInterface;
use Zend\Di\Exception\InvalidServiceConfigException;
use function class_exists;

class GeneratedInjectorDelegator
{
    /**
     * @param string $name
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback) : InjectorInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $aotConfig = $config['dependencies']['auto']['aot'] ?? [];
        $namespace = empty($aotConfig['namespace']) ? 'Zend\Di\Generated' : $aotConfig['namespace'];

        if (! is_string($namespace)) {
            throw new InvalidServiceConfigException('Provided namespace is not a string.');
        }

        $injector = $callback();

        $generatedInjector = $namespace . '\\GeneratedInjector';
        if (class_exists($generatedInjector)) {
            return new $generatedInjector($injector);
        }

        return $injector;
    }
}
