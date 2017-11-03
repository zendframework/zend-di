# Usage With Zend ServiceManager

zend-di is designed to play and integrate well with zend-servicemanager.
When you are using [zend-component-installer](https://docs.zendframework.com/zend-component-installer/),
you just need to install zend-di via composer and you're done.

## Service Factories For DI instances

zend-di ships with two service factories to provide the `Zend\Di\InjectorInterface` implementation.

* `Zend\Di\Container\ConfigFactory`: Creates a config instance by using the `"config"` service.
* `Zend\Di\Container\InjectorFactory`: Creates the injector instance that uses a
  `Zend\Di\ConfigInterface` service, if available.

```php

use Zend\Di;
use Zend\Di\Container;

$serviceManager->setFactory(Di\ConfigInterface::class, Container\ConfigFactory::class);
$serviceManager->setFactory(Di\InjectorInterface::class, Container\InjectorFactory::class);
```

## Abstract/Generic Service Factory

This component ships with an generic factory `Zend\Di\Container\AutowireFactory`. This factory
is suitable as abstract service factory for zend-servicemanager.

You can also use it to create instances with di using an IoC container (e.g. inside a service factory):

```php
use Zend\Di\Container\AutowireFactory;
(new AutowireFactory())->__invoke($container, MyClassname::class);
```

