<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class GeneratedInjectorDelegator implements DelegatorFactoryInterface
{
    /**
     * @param string $name
     * @return InjectorInterface
     */
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        array $options = null
    ) : InjectorInterface {
        $config = $container->has('config') ? $container->get('config') : [];
        $aotConfig = $config['dependencies']['auto']['aot'] ?? [];
        $namespace = $aotConfig['namespace'] ?? null;

        $injector = $callback();

        $generatedInjector = $namespace . '\\GeneratedInjector';
        if (class_exists($generatedInjector)) {
            return new $generatedInjector($injector);
        }

        return $injector;
    }
}
