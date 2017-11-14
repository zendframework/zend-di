# Migration Guide

Version 3 is the first new major release of zend-di, and contains a number of
backward incompatible changes. These were introduced to provide better performance
and stability.

This guide describes how to migrate from Version 2 to 3.

# What has changed?

This lists the most impacting changes and potential pitfalls when
upgrading to `zend-di` version 3.

* The injector now only supports constructor injections. If you require injections
  based on Aware-Interfaces or Method-Injections, you need to provide it on your
  own. You could do this by decorating the injector instance or using initializers
  in zend-servicemanager.
* `\Zend\Di\Di` is renamed to `\Zend\Di\InjectorInterface`. It also is no longer
  an IoC container which offers get/has. This is now offered by
  `Zend\Di\DefaultContainer` which implements `Psr\Container\ContainerInterface`.
  If you were using `\Zend\Di\Di` as IoC container please switch to
  `Zend\Di\DefaultContainer` or use it with [zend-servicemanager](cookbook/use-with-servicemanager.md).
* All programmatic and array-based Definitions were dropped. If you need custom
  definitions, you have to implement `\Zend\Di\Definition\DefinitionInterface`.
* The definition compiler was removed in favor of a [code generator](codegen.md),
  which offers better performance.
* Added PHP 7.1 Typesafety. All Interfaces and Classes are strongly typed.
* `Generator` and `GeneratorInstance` in `Zend\Di\ServiceLocator` were removed
  in favor of the [code generator](codegen.md), which creates zend-servicemanager
  compatible factories.

# Migrating from v2 to v3 with zend-mvc

When you are using zend-mvc you can follow these steps to upgrade:

1. Remove `zendframework/zend-servicemanager-di`from your composer.json
2. Change the version constraint for `zendframework/zend-di` to `^3.0`
3. Change the `Zend\ServiceManager\Di\Module` modules entry to `Zend\Di\Module`
4. If you are using any factory from zend-servicemanager-di, you may have to
   replace it with `Zend\Di\Container\AutowireFactory`
5. Migrate your di config to the new [configuration format](config.md).

# Migrating the configuration

The configuration is now expected in `$config['dependencies']['auto']`, where
`$config` is your `config` service.

The config service factory will automatically attempt to migrate legacy
configurations at runtime, which gives you some time to migrate your configs.
You can use `Zend\Di\LegacyConfig` to help migrating existing configs:

```php
use Zend\Di\LegacyConfig;

$migrated = new LegacyConfig($diConfigArray);
$code = var_export($migrated->toArray(), true);
```
