# PSR-11 Support

zend-di supports and implements [PSR-11 ContainerInterface](https://github.com/php-fig/container)
starting in version 3. It supports any implementation to obtain instances for
resolved dependencies.

zend-di ships with a very basic implementation of the container interface which
only uses the injector to creates instances and always shares services it
creates. We suggest you replace it with another implementation such as
[zend-servicemanager](https://docs.zendframework.com/zend-servicemanager/) for
more flexibility.
