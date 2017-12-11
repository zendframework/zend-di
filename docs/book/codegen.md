# Code Generator

zend-di comes with [Ahead-of-Time (AoT)](https://en.wikipedia.org/wiki/Ahead-of-time_compilation)
generators to create optimized code for production. These generators will
inspect the provided classes, resolve their dependencies, and generate factories
based on these results.

> ### Requirements
>
> This feature requires [zend-code](https://docs.zendframework.com/zend-code/),
> which you can add to your project using Composer:
>
> ```bash
> $ composer require zendframework/zend-code
> ```

# Generating an optimized injector

The `Zend\Di\CodeGenerator\InjectorGenerator` class offers an implementation to
generate an optimized injector based on the runtime configuration and a resolver
instance.

```php
use Zend\Di\Config;
use Zend\Di\Definition\RuntimeDefinition;
use Zend\Di\Resolver\DependencyResolver;
use Zend\Di\CodeGenerator\InjectorGenerator;

$config = new Config();
$resolver = new DependencyResolver(new RuntimeDefinition(), $config)
$generator = new InjectorGenerator($config, $resolver);

// It is highly recommended to set the container that is used at runtime:
$resolver->setContainer($container);
$generator->setOutputDirectory('/path/to/generated/files');
$generator->generate([
    MyClassA::class,
    MyClassB::class,
    // ...
]);
```

You can also utilize `Zend\Code\Scanner` to scan your code for classes:

```php
$scanner = new DirectoryScanner(__DIR__);
$generator->generate($scanner->getClassNames());
```

# MVC and Expressive integration

When you are using zend-di's `ConfigProvider` or MVC `Module`, you can
obtain the generator instance from Zend ServiceManager:

```php
$generator = $serviceManager->get(\Zend\Di\CodeGenerator\InjectorGenerator::class);
```

## AoT Config Options

The service factory uses options in your `config` service, located in `['dependencies']['auto']['aot']`.
If present this can be an associative array of options to create the code generator instance.
This array respects the following keys (unknown keys are ignored):

- `namespace`: This will be used as base namespace to prefix the namespace of the generated classes.
  It will be passed to the constructor of `Zend\Di\CodeGenerator\InjectorGenerator`. The default value
  is `Zend\Di\Generated` if this option is not provided.

- `directory`: The directory where the generated php files will be stored. If this value is not provided,
  you have to set it with the generator's `setOutputDirectory()` method before calling `generate()`.

Example:

Below is an example for configuring the generator factory:

```php
return [
    'dependencies' => [
        'auto' => [
            'aot' => [
                'namespace' => 'AppAoT\Generated',
                'directory' => __DIR__ . '/../gen',
            ],
        ],
    ],
];
```

