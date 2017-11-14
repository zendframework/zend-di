# Usage with PSR-11 containers

zend-di is designed to utilize and work with any IoC container thet implements
the PSR-11 `Psr\Container\ContainerInterface`. To achieve this you can pass any
container instance as the second parameter to the injector:

```php
use Zend\Di\Injector;

$injector = new Injector(null, $container);
```

From that point forwards, the injector will use the provided `$container` to
obtain the dependencies.

## Decorating the container

In the example above, the provided container may not utilize the injector to
create unknown instances, even when the classes are known to zend-di. It may
fail with an exception that dependencies could not be resolved.

If you want to pair the container with the injector and use the injector for
dependencies the container it is not aware of, you may decorate the original
container into a zend-di-aware implementation. As an example:

```php
namespace MyApp;

use Zend\Di\Injector;
use Psr\Container\ContainerInterface;

class MyContainer implements ContainerInterface
{
    private $container;

    private $injector;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $wrappedContainer;
        $this->injector = new Injector(null, $this);
    }

    public function has($name)
    {
        retrun $this->container->has($name) || $this->injector->canCreate($name);
    }

    public function get($name)
    {
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }

        $service = $this->injector->create($name);

        // You might make the service shared via the decorated container
        // as well:
        // $this->container->set($name, $service);

        return $service;
    }
}
```
