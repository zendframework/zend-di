# zend-di

[![Build Status](https://secure.travis-ci.org/zendframework/zend-di.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-di)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-di/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-di?branch=master)

zend-di provides autowiring to implement Inversion of Control (IoC) containers.
IoC containers are widely used to create object instances that have all
dependencies resolved and injected. Dependency Injection containers are one form
of IoC – but not the only form.

zend-di is designed to be simple, fast and reusable. It provides the following features:

- Constructor injection
- Autowiring:
  - Recursively through all dependencies
  - With configured type preferences
  - with configured injections
  - With injections passed in the create() call
- Code generators to create factories usable by other IoC containers like Zend\ServiceManager

It does __not__ provide:

- Setter, interface, property or any other injection method than constructor injection
- Support for factories
- Declaring shared/unshared instances
  - the injector always creates new instances
  - the default container always shares instances
- Support for variadic arguments in __construct

If you need these features combine it with another IoC container such as
[zend-servicemanager](https://docs.zendframework.com/zend-servicemanager/).

- File issues at https://github.com/zendframework/zend-di/issues
- Documentation is at https://docs.zendframework.com/zend-di/
