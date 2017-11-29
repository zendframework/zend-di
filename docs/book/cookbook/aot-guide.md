# Using AoT In A ZendFramework App

This guide will show you how you can use zend-di's AoT compiler
to make your ZendFramework application production ready.

You will learn how to:

* Add a script to run the compilation
* Use the generated injector with ServiceManager
* Use the generated factories with ServiceManager

## Create project and add zend-di

For this guide will use an [expressive application](https://docs.zendframework.com/zend-expressive/)
with zend-servicemanager as example.

If you already set up a project with zend-di, you can skip this step.

```bash
composer create-project zendframework/zend-expressive-skeleton zend-di-aot-example
```

Pick the components you want to use. As statet above, we will be using Zend ServiceManager
and a Modular layout for this example.

Now add zend-di with composer:

```bash
composer require zendframework/zend-di=^3.0
```

The component installer should ask you where to inject the config provider. Pick option 1
which usually is `config/config.php`. If not or you cannot use the component installer, you have to add it
manually by adding `\Zend\Di\ConfigProvider::class` to your config (in `config/config.php` for example):

```php
<?php

use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

// ...

$aggregator = new ConfigAggregator([
    // Add Zend\Di
    \Zend\Di\ConfigProvider::class,

    // ...
], $cacheConfig['config_cache_path']);

// ...
```

# Make your project ready for AoT

To follow the modular principle of our expressive app, we will
put the AoT related configurations and generated code in a separate module called `AppAoT`:

```bash
mkdir src/AppAoT/src
```

Create a config provider in `src/AppAoT/src/ConfigProvider.php`:

```
<?php

namespace AppAoT;

class ConfigProvider
{
    public function __invoke()
    {
        return [];
    }
}
```

Add it at the beginning of your `config/config.php`:

```php
$aggregator = new ConfigAggregator([
    \AppAoT\ConfigProvider::class
    // Add Zend\Di
    \Zend\Di\ConfigProvider::class,

    // ...
]);
```

Now you have to add `src/AppAoT/src/` for `AppAoT\\` to the psr-4 autoload section
in your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/App/src/",
            "AppAoT\\": "src/AppAoT/src/"
        }
    },
    ...
}
```

Finally update your autoloader:

```bash
composer dump-autoload
```

