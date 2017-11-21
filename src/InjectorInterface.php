<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

/**
 * Interface that defines the dependency injector
 */
interface InjectorInterface
{
    /**
     * Check if this dependency injector can handle the given class
     *
     * @param string $name
     * @return bool
     */
    public function canCreate(string $name) : bool;

    /**
     * Create a new instance of a class or alias
     *
     * @param mixed $name Class name or service alias
     * @param array $options Parameters used for instanciation
     * @return object The resulting instace
     * @throws Exception\ExceptionInterface When an error occours during instanciation
     */
    public function create(string $name, array $options = []);
}
