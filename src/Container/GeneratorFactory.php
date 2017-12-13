<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Container;

use Psr\Container\ContainerInterface;
use Zend\Di\CodeGenerator\InjectorGenerator;
use Zend\Di\ConfigInterface;
use Zend\Di\Definition\RuntimeDefinition;
use Zend\Di\Resolver\DependencyResolver;

class GeneratorFactory
{
    private function getConfig(ContainerInterface $container) : ConfigInterface
    {
        if ($container->has(ConfigInterface::class)) {
            return $container->get(ConfigInterface::class);
        }

        return (new ConfigFactory())->create($container);
    }

    public function create(ContainerInterface $container) : InjectorGenerator
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $diConfig = $this->getConfig($container);
        $aotConfig = $config['dependencies']['auto']['aot'] ?? [];
        $resolver = new DependencyResolver(new RuntimeDefinition(), $diConfig);
        $namespace = $aotConfig['namespace'] ?? null;

        $resolver->setContainer($container);
        $generator = new InjectorGenerator($diConfig, $resolver, $namespace);

        if (isset($aotConfig['directory'])) {
            $generator->setOutputDirectory($aotConfig['directory']);
        }

        return $generator;
    }

    public function __invoke(ContainerInterface $container) : InjectorGenerator
    {
        return $this->create($container);
    }
}
