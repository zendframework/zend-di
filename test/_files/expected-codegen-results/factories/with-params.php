<?php
/**
 * Generated factory for \ZendTest\Di\TestAsset\Constructor\MixedArguments
 */

namespace ZendTest\Di\Generated\Factory\ZendTest\Di\TestAsset\Constructor;

use Psr\Container\ContainerInterface;
use Zend\Di\CodeGenerator\FactoryInterface;

use function array_key_exists;
use function is_array;

final class MixedArgumentsFactory implements FactoryInterface
{
    public function create(ContainerInterface $container, array $options = [])
    {
        if (empty($options)) {
            $args = [
                $container->get('ZendTest\\Di\\TestAsset\\Constructor\\NoConstructor'), // objectDep
                null, // anyDep
            ];
        } else {
            $args = [
                array_key_exists('objectDep', $options)? $options['objectDep'] : $container->get('ZendTest\\Di\\TestAsset\\Constructor\\NoConstructor'),
                array_key_exists('anyDep', $options)? $options['anyDep'] : null,
            ];
        }

        return new \ZendTest\Di\TestAsset\Constructor\MixedArguments(...$args);
    }

    public function __invoke(ContainerInterface $container, $name = null, array $options = null)
    {
        if (is_array($name) && ($options === null)) {
            $options = $name;
        }

        return $this->create($container, $options ?? []);
    }
}
