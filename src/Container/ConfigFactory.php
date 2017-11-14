<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Container;

use Psr\Container\ContainerInterface;
use Zend\Di\Config;
use Zend\Di\ConfigInterface;
use Zend\Di\LegacyConfig;

/**
 * Factory implementation for creating the definition list
 */
class ConfigFactory
{
    /**
     * @param ContainerInterface $container
     * @return \Zend\Di\Config
     */
    public function create(ContainerInterface $container): ConfigInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $data = (isset($config['dependencies']['auto'])) ? $config['dependencies']['auto'] : [];

        if (isset($config['di'])) {
            trigger_error('Detected legacy DI configuration, please upgrade to v3.', E_USER_DEPRECATED);

            $legacyConfig = new LegacyConfig($config['di']);
            $data = array_merge_recursive($legacyConfig->toArray(), $data);
        }

        return new Config($data);
    }

    /**
     * Make the instance invokable
     */
    public function __invoke(ContainerInterface $container): ConfigInterface
    {
        return $this->create($container);
    }
}