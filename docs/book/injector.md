# Injector

The `Zend\Di\Injector` is responsible for creating instances by providing the
dependencies required by the class.

The dependencies are resolved by analyzing the constructor parameters of the
requested class via reflection. For parameters defined with a class or interface
typehint, the configured preferences are taken into account.

A `Zend\Di\ConfigInterface` can be provided to configure the injector.
See the [Configuration](config.md) chapter for details.

## Create instances

Instances can be created by calling `create()`:

```php
use Zend\Di\Injector;

$injector = new Injector()
$injector->create(MyClass::class);
```

## Create instances with parameters

You can also pass construction parameters when calling create:

```php
$injector->create(MyDbAdapter::class, [
    'username' => 'johndoe',
]);
```

Parameters passed to `create()` will overwrite any configured injection for the
requested class.

Generally the following behavior applies for parameter values that are not
`ValueInjection` or `TypeInjection` instances:

- If the parameter has a class/interface typehint:
  - string values will be wrapped into a `TypeInjection` instance
  - objects are wrapped into a `ValueInjection` instance
  - everything else will fail with an exception.

- If the parameter has a scalar or pseudo-type typehint (e.g. string, int,
  iterable, callable, etc ...), the value will be wrapped in a `ValueInjection`.

- If the parameter has no typehint at all, the value will be wrapped into a
  `ValueInjection`.


### Examples

```php
// Assume the following classes
class Foo
{}

class SpecialFoo extends Foo
{}

class Bar
{
    public function __construct(Foo $foo, $type = null)
    {}
}

// Usage
use Zend\Di\Resolver\ValueInjection;
use Zend\Di\Resolver\TypeInjection;

// Creates Bar with an instance of SpecialFoo from the IoC container:
$injector->create(Bar::class, [
    'foo' => SpecialFoo::class,
]);

// Creates Bar with the given instance of SpecialFoo bypassing the IoC
// container:
$injector->create(Bar::class, [
    'foo' => ValueInjection(new SpecialFoo()),
]);

// Creates Bar with an instance of Foo and the string literal 'SpecialFoo' for
// $type:
$injector->create(Bar::class, [
    'type' => SpecialFoo::class,
]);

// Creates Bar with an instance of Foo and an instance of SpecialFoo from the
// IoC container for $type:
$injector->create(Bar::class, [
    'type' => new TypeInjection(SpecialFoo::class),
]);
```

Refer to the [Parameters section in the Configuration chapter](config.md#parameters)
for all possibilities of how parameters can be declared.

## Check if a type is creatable

If you uncertain whether or not the injector can create a specific type, you can
test it with the `canCreate()` method.

For example, if you were to consume the class name in a generic service factory
for zend-servicemanager:

```php
use Zend\Di\Injector;

/** @var \Zend\ServiceManager\ServiceManager $container */
$factory = function($container, $requestedName, array $options = null) {
    $injector = $container->get(Injector::class);

    if (! $injector->canCreate($requestedName)) {
        throw new \RuntimeException('Bad service name');
    }

    return $injector->create($requestedName, $options? : []);
};

$serviceManager->setFactory('Foo', $factory);
$serviceManager->setFactory('Bar', $factory);
$serviceManager->setFactory(stdClass::class, $factory);
```
