# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
