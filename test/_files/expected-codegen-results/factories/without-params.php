<?php
/**
 * Generated factory for \ZendTest\Di\TestAsset\A
 */

declare(strict_types=1);

namespace ZendTest\Di\Generated\Factory\ZendTest\Di\TestAsset;

use Psr\Container\ContainerInterface;
use Zend\Di\CodeGenerator\FactoryInterface;

use function is_array;

final class AFactory implements FactoryInterface
{
    public function create(ContainerInterface $container, array $options = [])
    {
        return new \ZendTest\Di\TestAsset\A();
    }

    public function __invoke(ContainerInterface $container, $name = null, array $options = null)
    {
        if (is_array($name) && $options === null) {
            $options = $name;
        }

        return $this->create($container, $options ?? []);
    }
}
