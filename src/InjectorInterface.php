<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
     * @param   string $name
     * @return  bool
     */
    public function canCreate($name);

    /**
     * Create a new instance of a class or alias
     *
     * @param  mixed   $name                   Class name or service alias
     * @param  array   $options                Parameters used for instanciation
     * @return object  The resulting instace
     * @throws Exception\ExceptionInterface When an error occours during instanciation
     */
    public function create($name, array $options = []);
}
