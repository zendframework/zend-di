# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.0.0 - WIP

### Added

- `Zend\Di\DefaultContainer` that implements `Psr\Container\ContainerInterface`
  * Can act as a standalone IoC container
  * Provides `build()` to be signature compatible with `Zend\ServiceManager\ServiceManager`
- Renamed `Zend\Di\DependencyInjectionInterface` to `Zend\Di\InjectorInterface`. It defines
  the injector to create new instances based on a class or alias name.
  * `newInstance()` Changed to `create()`
  * `has()` Changed to `canCreate()`
  * Removed `get()`
- `Zend\Di\Injector` as implementation of `Zend\Di\InjectorInterface`
  * Is designed to incorporate with `Psr\Container\ContainerInterface`
  * Utilizes `Zend\Di\Resolver\DependencyResolverInterface`
- Moved strategies to resolve method parameters to `Zend\Di\Resolver`
- PHP 7.1 Typesafety
- Classes to wrap value and type injections
- Support for zend-component-installer
- `Zend\Di\ConfigInterface` to implement custom configurations
- Code generator for generating a pre-resolved injector and factories

### Deprecated

- Nothing

### Removed

- Support for PHP < 7.1
- `Zend\Di\Defintion\CompilerDefinition` in favour of `Zend\Di\CodeGenerator`.
- `Zend\Di\InstanceManager`, `Zend\Di\ServiceLocator`, `Zend\Di\ServiceLocatorInterface`
  and `Zend\Di\LocatorInterface` in favour of `Psr\Container\ContainerInterface`
- `Zend\Di\Di` is removed in favour of `Zend\Di\DefaultContainer`
- `Zend\Di\DefintionList`
- `Zend\Di\Definition\BuilderDefinition`
- `Zend\Di\Definition\ArrayDefinition`
- Parameters passed to `newInstance()` will only be used for constructing the requested class and no longer be forwarded to nested instanciations.
- `get()` does no longer support a `$parameters` array, `newInstance()` still does
- Removed setter/method injections
- Generators in `Zend\Di\ServiceLocator` in favor of `Zend\Di\CodeGenerator`

### Fixed

- [#6](https://github.com/zendframework/zend-di/pull/6) Full ZF3 Compatibility
- [#20](https://github.com/zendframework/zend-di/pull/20) Update composer deps and travis config
- [#17](https://github.com/zendframework/zend-di/pull/17) Fix mkdocs config (src_dir is deprecated)
- [#18](https://github.com/zendframework/zend-di/issues/18) Di Runtime Compiler Definition

## 2.7.0 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.2 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

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
