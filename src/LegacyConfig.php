<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Parameters;

/**
 * Provides a migration config from zend-di 2.x configuration arrays
 */
class LegacyConfig extends Config
{
    public function __construct($config)
    {
        parent::__construct([]);

        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (! is_array($config)) {
            throw new Exception\InvalidArgumentException('Config data must be an array or implement Traversable');
        }

        if (isset($config['instance'])) {
            $this->configureInstance($config['instance']);
        }
    }

    private function prepareParametersArray($parameters, string $class)
    {
        $prepared = [];

        foreach ($parameters as $key => $value) {
            if (strpos($key, ':') !== false) {
                trigger_error('Full qualified parameter positions are no longer supported', E_USER_DEPRECATED);
            }

            $prepared[$key] = $value;
        }

        return $prepared;
    }

    private function configureInstance($config)
    {
        foreach ($config as $target => $data) {
            switch ($target) {
                case 'aliases':
                case 'alias':
                    foreach ($data as $name => $class) {
                        if (class_exists($class) || interface_exists($class)) {
                            $this->setAlias($name, $class);
                        }
                    }
                    break;

                case 'preferences':
                case 'preference':
                    foreach ($data as $type => $pref) {
                        $preference = is_array($pref) ? array_pop($pref) : $pref;
                        $this->setTypePreference($type, $preference);
                    }
                    break;

                default:
                    $config = new Parameters($data);
                    $parameters = $config->get('parameters', $config->get('parameter'));

                    if (is_array($parameters) || ($parameters instanceof Traversable)) {
                        $parameters = $this->prepareParametersArray($parameters, $target);
                        $this->setParameters($target, $parameters);
                    }
                    break;
            }
        }
    }

    /**
     * Export the configuraton to an array
     */
    public function toArray() : array
    {
        return [
            'preferences' => $this->preferences,
            'types' => $this->types,
        ];
    }
}
