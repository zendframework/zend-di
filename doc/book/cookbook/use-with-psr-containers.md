# Usage with PSR-11 containers

zend-di is designed to utilize and work with any IoC container thet implements the PSR-11 interface.
To achieve this you can pass the container instance as second parameter to the injector:

```php
use Zend\Di\Injector;

$injector = new Injector(null, $container);
```

From there on the injector will use the provided `$container` to obtain the dependencies.

## Wrapping the container

In the example above the provided container may not utilize the injector to create unknown
instances, even when the classes are known. It may fail with an exception that dependencies
could not be resolved.

If you want to pair the container with the injector and use the injector for dependencies
the container it is not aware of, you may wrap the original container into a di aware implementation
as in the following example:

```php

namespace MyApp;

use Zend\Di\Injector;
use Psr\Container\ContainerInterface;

class MyContainer implements ContainerInterface
{
    private $wrapped;

    private $injector;

    public function __construct(ContainerInterface $wrappedContainer)
    {
        $this->wrapped = $wrappedContainer;
        $this->injector = new Injector(null, $this);
    }

    public function has($name)
    {
        retrun $this->wrapped->has($name) || $this->injector->canCreate($name);
    }

    public function get($name)
    {
        if ($this->wrapped->has($name)) {
            return $this->wrapped->get($name);
        }

        $service = $this->injector->create($name);

        // You can make the service shared via the wrapped container
        // or anyhow else ...
        // $this->container->set($name, $service);

        return $service;
    }
}
```
