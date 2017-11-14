# Configuration

The configuration can be provided as an associative array by constructing `Zend\\Di\\Config`.
The configuration defines how types are constructed and dependencies should be
resolved. A type may be an actual class name or an alias to a class name.

The configuration array respects the following keys (unknown keys are simply ignored):

* `preferences`: Associative nested array that maps class or interface names to a service name
  that should be used to provied a dependency. See the Type Preferences section below for details.
* `types`: Associative array defining how classes or aliases should be constructed. Each key
  in this array is a class or alias name and its value is another associative array with the
  following keys:
  - `preferences`: The same as `preferences` above, but only for the associated class.
  - `parameters`: Associative array declaring the values to inject for the declared construction parameters.
    Each key is the parameter name as declared in the constructor method of the associated class name.
    See the parameters section below for details.
  - `aliasOf`: String that contains a class name. It declares that the associated key is an alias
    of the given class name. This class must exist. It cannot not be another alias.

Here is an example of how the injector config can be created:

```php
$config = new \Zend\Di\Config([
    // Declares global preferences to use when resolving
    // dependencies of the specified type
    'preferences' => [
        // A map of classname => preferred type
        MyInterface::class => MyImplementation::class
    ],

    // Declares how types should be constructed.
    // This also allows declaring aliases of a specific class
    'types' => [
        ClassName::class => [
            // Declaration in the same way as global preferences
            // but these will aply when the type of the associated key
            // should be instanciated
            'preferences' => [],

            // Constructor parameters to inject. This option will define
            // the injections directly by the parameter name of the constructor
            // used as key.
            // If the parameter is Typehinted by a class/interface name, you can
            // provide the injection by string. The injector will use the ioc
            // container to obtain it.
            'parameters' => [
                'foo' => 'bar'
            ]
        ],

        // Define an alias
        'Alias.Name' => [
            'aliasOf' => ClassName::class,

            'preferences' => [],
            'parameters' => []
        ]
    ]
]);
```

## Type Preferences

In many cases, you might be using interfaces as type hints as opposed to
concrete types. Even though type preferences are not limited to interfaces or abstract
class names, they provide hints to the injector on how such types should be resolved.

The resolver will look up the name finally passed to the container in the following way
(the first match will be used):

1. The preference defined in the type configuration of  the class if it satifies
   the typehint (implements, extends or aliasOf)
2. If there is a global preference defined and it satifies the typehint
3. Use the typehintet name directly

```php

// Assume the following classes are declared:

interface FooInterface
{}

class Foo implements FooInterface
{}

class SpecialFoo implements FooInterface
{}

class Bar
{}

class MyClass
{
    public function __construct(FooInterface $foo)
    {
        // ...
    }
}

// With the following configuration:

use Zend\Di\Injector;
use Zend\Di\Config;

$injector = new Injector(new Config([
    'preferences' => [
        FooInterface::class => Foo::class
    ]
    'types' => [
        'MyClass.A' => [
            'typeOf' => MyClass::class
            'preferences' => [
               FooInterface::class => SpecialFoo::class,
            ]
        ],
        'MyClass.B' => [
            'typeOf' => MyClass::class
            'preferences' => [
               FooInterface::class => Bar::class,
            ]
        ],
    ]
]);


// The results are:
$a = $injector->create(MyClass::class); // Constructed with Foo
$b = $injector->create('MyClass.A'); // Constructed with SpecialFoo
$c = $injector->create('MyClass.B'); // Constructed with Foo (since Bar does not satisfy FooInterface)

```


## Parameters

In contrast of type preferences, the resolver will not perform checks if the provided value
satisfies the required type. It will be used directly to inject the value.

There are a couple of ways to define injections.

* An IoC container service name as string: This is only possible if the required type is a
  class or interface. For other types (scalar, iterable, callable, etc) or typeless parameters
  the string value is passed __as is__.
* An instance of `Zend\Di\Resolver\ValueInjection`: Injects the value returned by `getValue()`
  as is.
* An instance of `Zend\Di\Resolver\TypeInjection`: Obtains the injected value from the IoC
  container by passing the return value of `getType()` to the container's `get()` method.
* The string literal `'*'`: This requests the injector to ignore any previously defined parameter
  and use the type preference resolution as described in Type Preferences.
* Any other value will be used as is and encapsulated in a `Zend\Di\Resolver\ValueInjection`.
  If the provided value's type does not fit the required parameter type, an exception is thrown.

## Aliases

Aliases allow you to configure the same class with different construction options. Aliases can
directly be created with the injector or declared as type preferences.

An alias must refer to an actual class or an interface, therefore you cannot declare aliases for another alias.

For example the following the following class should be instanciated in two different ways:

```php

// Assume the following classes are declared

class Foo
{}

class SpecialFoo extends Foo
{}

class MyClass
{
    public function __construct(Foo $foo, string $bar)
    {
        // ...
    }
}

// With the following injection config:

use Zend\Di\Injector;
use Zend\Di\Config;

$injector = new Injector(new Config([
    'types' => [
        MyClass::class => [
            'parameters' => [
               'foo' => SpecialFoo::class,
               'bar' => 'Stringvalue'
            ]
        ],
        'MyClass.Alias' => [
            'typeOf' => MyClass::class,
            'parameters' => [
               'foo' => '*',
               'bar' => 'Stringvalue'
            ]
        ]
    ]
]);


// The results are:
$a = $injector->create(MyClass::class); // Constructed with SpecialFoo
$b = $injector->create('MyClass.Alias'); // Constructed with Foo (since there are no type preferences for Foo)
```
