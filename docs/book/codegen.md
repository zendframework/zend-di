# Code Generator

`Zend\Di` comes with [AoT](https://en.wikipedia.org/wiki/Ahead-of-time_compilation)
generators to create optimized code for production.
These generators will inspect the provided classes, resolve its dependencies and generate
factories based on these results.

__NOTE:__ This feature requires [zend-code](https://docs.zendframework.com/zend-code/).

## Generating an optimized injector

The `Zend\Di\CodeGenerator\InjectorGenerator` offers an implementation to generate
an optimized Injector based on the runtime configuration and a resolver instance.

```php
use Zend\Di\Config;
use Zend\Di\Definition\RuntimeDefinition;
use Zend\Di\Resolver\DependencyResolver;
use Zend\Di\CodeGenerator\InjectorGenerator;

$config = new Config();
$resolver = new DependencyResolver(new RuntimeDefinition(), $config)
$generator = new InjectorGenerator($config, $resolver);

// It is highly recommended to set the container that is used at runtime
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
