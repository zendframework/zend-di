# PSR-11 Support

zend-di stating from version 3.0 and up utilizes the [psr/container](https://github.com/php-fig/container)
interfaces. It supports any implementation to obtain the instances of the resolved dependencies.

zend-di ships with a very basic implementation of the container interface which only uses the
injector to creates instances and always shares once created services. We suggest you replace it
with another implementation like [zend-servicemanager](https://docs.zendframework.com/zend-servicemanager/) for more flexibility.
