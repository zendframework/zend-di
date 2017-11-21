<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Resolver;

use Psr\Container\ContainerInterface;

/**
 * Interface for implementing dependency resolvers
 *
 * The dependency resolver is used by the dependency injector or the
 * code generator to gather the types and values to inject
 */
interface DependencyResolverInterface
{
    /**
     * Set the ioc container
     *
     * @param ContainerInterface $container The ioc container to utilize for
     *     checking for instances
     * @return self Should provide a fluent interface
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Resolve a type prefernece
     *
     * @param string $type The type/class name of the dependency to resolve the
     *     preference for
     * @param string $context The typename of the instance that is created or
     *     in which the dependency should be injected
     * @return string Returns the preferred type name or null if there is no
     *     preference
     */
    public function resolvePreference(string $type, ?string $context = null) : ?string;

    /**
     * Resolves all parameters for injection
     *
     * @param string $class The class name to resolve the parameters for
     * @param array $parameters Parameters to use as provided.
     * @return array Returns the injection parameters as positional array. This
     *     array contains either TypeInjection or ValueInjection instances
     * @throws \Zend\Di\Exception\MissingPropertyException  When a parameter
     *     could not be resolved
     */
    public function resolveParameters(string $class, array $parameters = []) : array;
}
