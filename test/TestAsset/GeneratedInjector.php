<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2019 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\TestAsset;

use Zend\Di\InjectorInterface;

class GeneratedInjector implements InjectorInterface
{
    /** @var InjectorInterface */
    private $injector;

    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    public function getInjector() : InjectorInterface
    {
        return $this->injector;
    }

    public function canCreate(string $name) : bool
    {
        return $this->injector->canCreate($name);
    }

    public function create(string $name, array $options = [])
    {
        return $this->injector->create($name, $options);
    }
}
