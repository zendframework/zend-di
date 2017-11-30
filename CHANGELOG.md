# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.0.0 - 2017-11-30

### Added

- `Zend\Di\DefaultContainer` implementing `Psr\Container\ContainerInterface`:
  - Can act as a standalone IoC container.
  - Provides `build()` to be signature compatible with `Zend\ServiceManager\ServiceManager`.

- `Zend\Di\Injector` implementing `Zend\Di\InjectorInterface`
  - Designed to compose a `Psr\Container\ContainerInterface` implementation for
    purposes of resolving dependencies. By default, this is the `DefaultContainer`
    implementation.
  - Utilizes `Zend\Di\Resolver\DependencyResolverInterface` to resolve arguments
    to their types.

- PHP 7.1 type safety.

- Classes to wrap value and type injections.

- Support for zend-component-installer. This allows it to act as a standalone
  config-provider or zend-mvc module, and eliminates the need for
  zend-servicemanager-di.

- `Zend\Di\ConfigInterface` to allow providing custom configuration.

- Code generator for generating a pre-resolved injector and factories.

### Changed

- Renames `Zend\Di\DependencyInjectionInterface` to `Zend\Di\InjectorInterface`.
  It defines the injector to create new instances based on a class or alias
  name.
  - `newInstance()` changes to `create()`.
  - `has()` changes to `canCreate()`.
  - Removes `get()`.

- Moves strategies to resolve method parameters to `Zend\Di\Resolver`

### Deprecated

- Nothing

### Removed

- Support for PHP versions less than 7.1

- Support for HHVM.

- `Zend\Di\Defintion\CompilerDefinition` in favour of `Zend\Di\CodeGenerator`.

- `Zend\Di\InstanceManager`, `Zend\Di\ServiceLocator`, `Zend\Di\ServiceLocatorInterface`
  and `Zend\Di\LocatorInterface` in favor of `Psr\Container\ContainerInterface`.

- `Zend\Di\Di` is removed in favour of `Zend\Di\DefaultContainer`.

- `Zend\Di\DefinitionList`

- `Zend\Di\Definition\BuilderDefinition`

- `Zend\Di\Definition\ArrayDefinition`

- Parameters passed to `newInstance()` will only be used for constructing the
  requested class and no longer be forwarded to nested objects.

- `get()` no longer supports a `$parameters` array; `newInstance()` still does.

- Removed setter/method injections.

- Generators in `Zend\Di\ServiceLocator` in favor of `Zend\Di\CodeGenerator`.

### Fixed

- [#6](https://github.com/zendframework/zend-di/pull/6) Full ZF3 Compatibility.
- [#18](https://github.com/zendframework/zend-di/issues/18) DI Runtime Compiler
  Definition.

## 2.6.1 - 2016-04-25

### Added

- Adds all existing documentation and publishes it at
  https://zendframework.github.io/zend-di/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/zendframework/zend-di/pull/3) fixes how
  `InstanceManager::sharedInstancesWithParams()` behaves when multiple calls are
  made with different sets of parameters (it should return different instances
  in that situation).

## 2.6.0 - 2016-02-23

### Added

- [#16](https://github.com/zendframework/zend-di/pull/16) adds container-interop
  as a dependency, and updates the `LocatorInterface` to extend
  `Interop\Container\ContainerInterface`. This required adding the following
  methods:
  - `Zend\Di\Di::has()`
  - `Zend\Di\ServiceLocator::has()`

### Deprecated

- Nothing.

### Removed

- [#15](https://github.com/zendframework/zend-di/pull/15) and
  [#16](https://github.com/zendframework/zend-di/pull/16) remove most
  development dependencies, as the functionality could be reproduced with
  generic test assets or PHP built-in classes. These include:
  - zend-config
  - zend-db
  - zend-filter
  - zend-log
  - zend-mvc
  - zend-view
  - zend-servicemanager

### Fixed

- [#16](https://github.com/zendframework/zend-di/pull/16) updates the try/catch
  block in `Zend\Di\Di::resolveMethodParameters()` to catch container-interop
  exceptions instead of the zend-servicemanager-specific exception class. Since
  all zend-servicemanager exceptions derive from container-interop, this
  provides more flexibility in using any container-interop implementation as a
  peering container.
