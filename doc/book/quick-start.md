# Quick Start

The DI component provides a simple and easy-to-use auto wiring strategy which implements
[constructor injection](https://en.wikipedia.org/wiki/Dependency_injection#Constructor_injection).

It utilizes PSR-11 Containers to obtain required services, so it can be paired with any IoC container
that implements this interface such as [zend-servicemanager](https://docs.zendframework.com/zend-servicemanager/).

## 1. Installation

If you haven't already, [install Composer](https://getcomposer.org/).
Once you have, you can install the service manager:

```bash
$ composer install zendframework/zend-di
```

## 2. Configuring the injector

Cou can now create and configure an injector instance. The injector accepts an instance of
`Zend\Di\ConfigInterface`. This can be provided by passing `Zend\Di\Config`, which accepts a simple array:

```php
use Zend\Di\Injector;
use Zend\Di\Config;

$injector = new Injector(new Config([
    'preferences' => [
        MyInterface::class => MyImplementation::class
    ]
]));
```

This config implementation accepts a veriety of options. Refer to the [Configuration](config.md) section for
full details.

## 3. Creating instances

Finally you can create new instances of a specific class or alias by using the `create()` method:

```php
$instance = $injector->create(MyClass::class);
```

The only precondition is: The class you are passing to create must exist (or be autoloadable).
If this is not the case, the injector will fail with an exception.

The `create()` call will _always_ create a new instance of the given class. If you
need a shared instance, you can utilize the associated IoC container, which implements the PSR-11 interface:

```php
$sharedInstance = $di->getContainer()->get(MyClass::class);
```

The default container implementation is very limited and you should use one that provides more features like
[Zend ServiceManager](https://docs.zendframework.com/zend-servicemanager/). Refer to the
[Usage with PSR-Containers](cookbook/use-with-psr-containers.md) and
[Usage with Zend ServiceManager](cookbook/use-with-servicemanager.md) sections for details.
