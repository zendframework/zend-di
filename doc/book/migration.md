# Migration Guide

zend-di was first introduced in Zend Framework 2.0.0. Its API remained the
same throughout that version.

Version 3 is the first new major release of zend-di, and contains a number of
backwards compatibility breaks. These were introduced to provide better performance
and stability.

# Injection and Object construction

v2 was able to inject dependencies via constructor as well as via
methods. It also was able to use annotations as well as
interfaces for hinting which methods are injectable.

it was also possible to construct objects via a static factory method.

This allowed complex constructions vie a even more complex configuration
and type definition.

__In v3 only constructor injection is supported. Method injection is no longer supported__

If you require instanciation on a different way or additional injections via method you must
now provide or wrap zend-di into a PSR-11 compatible IoC container that will handle this.

This change makes zend-di faster, less error prone and simplifies the configuration a lot.

# ServiceManager Integration

Due to its change to PSR-11 awareness, the [zend-servicemanager-di](https://docs.zendframework.com/zend-servicemanager-di/)
integration is no longer needed in v3. In fact is is now a conflicting dependency in
zend-di's `composer.json`.

__v3 ships with its own integration.__

See the [Usage With Zend ServiceManager](cookbook/use-with-servicemanager.md) for details.

# Configuration

The configuration has changed completely.

* There is no longer an `instance` config
* There is no longer a `definition` config
* Type `preferences` are no longer arrays
* Preferences can now be declared global or on type level
* A type configuration was added


# Definition

All kinds of definition have been dropped in v3, zend-di now only ships with a `RuntimeDefinition`.
The change to constructor injection only, makes complex definitions obsolete.

If you have special cases you should use an IoC Container like zend-servicemanager paired with zend-di
to handle these.

# InstanceManager and ServiceLocator

In v3 this was dropped in favour of PSR-11 containers.

# Zend\Di\Di

This used to be the container in v2.

In v3 this was replaced by `Zend\Di\Injector` which is actually more a factory than container.
However, the injector references a PSR-11 container that can be used.

# Mvc integration

The configuration was placed in the `di` key of the `Config` service for Mvc. While still supported
this is now deprecated. The configuration should now be paced in `$config['depenencies']['auto']`
as it is the case for Expressive.
