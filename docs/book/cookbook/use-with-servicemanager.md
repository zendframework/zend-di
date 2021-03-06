# Usage With zend-servicemanager

zend-di is designed to play and integrate well with zend-servicemanager.  When
you are using [zend-component-installer](https://docs.zendframework.com/zend-component-installer/),
you just need to install zend-di via composer and you're done.

## Service Factories For DI instances

zend-di ships with two service factories to provide the
`Zend\Di\InjectorInterface` implementation.

- `Zend\Di\Container\ConfigFactory`: Creates a config instance by using the `"config"` service.

- `Zend\Di\Container\InjectorFactory`: Creates the injector instance that uses a
  `Zend\Di\ConfigInterface` service, if available.

```php
use Zend\Di;
use Zend\Di\Container;

$serviceManager->setFactory(Di\ConfigInterface::class, Container\ConfigFactory::class);
$serviceManager->setFactory(Di\InjectorInterface::class, Container\InjectorFactory::class);
```

## Abstract/Generic Service Factory

This component ships with an generic factory
`Zend\Di\Container\AutowireFactory`. This factory is suitable as an abstract
service factory for use with zend-servicemanager.

You can also use it to create instances with zend-di using an IoC container
(e.g. inside a service factory):

```php
use Zend\Di\Container\AutowireFactory;
(new AutowireFactory())->__invoke($container, MyClassname::class);
```

Or you can use it as factory in your service configuration directly:

```php
return [
    'factories' => [
        SomeClass::class => \Zend\Di\Container\AutowireFactory::class,
    ],
];
```


## Service Factory For AoT Code Generation

zend-di also provides a factory for `Zend\Di\CodeGenerator\InjectorGenerator`.
This factory (`Zend\Di\Container\GeneratorFactory`) is also auto registered by
the `Module` and `ConfigProvider` classes for zend-mvc and Expressive.
