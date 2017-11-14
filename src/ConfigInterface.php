<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

/**
 * Provides the instance and resolver configuration
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Check if the provided type name is aliased
     *
     * @param  string $name
     * @return bool
     */
    public function isAlias(string $name): bool;

    /**
     * @return string[]
     */
    public function getConfiguredTypeNames(): array;

    /**
     * Returns the actual class name for an alias
     *
     * @param  string $name
     * @return string
     */
    public function getClassForAlias(string $name): ?string;

    /**
     * Returns the instanciation parameters for the given type
     *
     * @param   string  $type   The alias or class name
     * @return  array           The configured parameter hash
     */
    public function getParameters(string $type): array;

    /**
     * Set the instanciation parameters for the given type
     *
     * @param string $type
     * @param array $params
     */
    public function setParameters(string $type, array $params);

    /**
     * Configured type preference
     *
     * @param  string   $type
     * @param  string   $contextClass
     * @return string
     */
    public function getTypePreference(string $type, ?string $contextClass = null): ?string;
}