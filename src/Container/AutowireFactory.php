<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Container;

use Psr\Container\ContainerInterface;
use Zend\Di\Exception;
use Zend\Di\InjectorInterface;

/**
 * Create instances with autowiring
 */
class AutowireFactory
{
    /**
     * Retrieves the injector from a container
     *
     * @param ContainerInterface $container The container context for this factory
     * @return InjectorInterface The dependency injector
     * @throws Exception\RuntimeException When no dependency injector is available
     */
    private function getInjector(ContainerInterface $container)
    {
        $injector = $container->get(InjectorInterface::class);

        if (! $injector instanceof InjectorInterface) {
            throw new Exception\RuntimeException(
                'Could not get a dependency injector form the container implementation'
            );
        }

        return $injector;
    }

    /**
     * Check creatability of the requested name
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (! $container->has(InjectorInterface::class)) {
            return false;
        }

        return $this->getInjector($container)->canCreate((string)$requestedName);
    }

    /**
     * Create an instance
     */
    public function create(ContainerInterface $container, string $requestedName, ?array $options = null)
    {
        return $this->getInjector($container)->create($requestedName, $options ?: []);
    }

    /**
     * Make invokable and implement the zend-service factory pattern
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->create($container, (string)$requestedName, $options);
    }
}
