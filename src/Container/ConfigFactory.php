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


/**
 * Factory implementation for creating the definition list
 */
class ConfigFactory
{
    /**
     * @param ContainerInterface $container
     * @return \Zend\Di\Config
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config')? $container->get('config') : [];
        $data = (isset($config['dependencies']['auto']))? $config['dependencies']['auto'] : [];

        if (isset($config['di'])) {
            $data = array_merge_recursive($config['di'], $data);
        }

        // Legacy
        if (isset($data['instances'])) {
            trigger_error('The "instances" key is deprecated, use "types" instead.', E_USER_DEPRECATED);

            if (!isset($data['types'])) {
                $data['types'] = $data['instances'];
            } else {
                $data['types'] = array_merge_recursive($data['instances'], $data['types']);
            }
        }

        return new Config($data);
    }
}
