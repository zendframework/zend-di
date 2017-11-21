<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Resolver;

use ReflectionObject;
use Zend\Di\Exception\RuntimeException;

/**
 * Wrapper for values that should be directly injected
 */
class ValueInjection extends AbstractInjection
{
    /**
     * Holds the value to inject
     *
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param string $state
     */
    public static function __set_state($state) : self
    {
        return new self($state['value']);
    }

    /**
     * Get the value to inject
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Exports the encapsulated value to php code
     *
     * @return string
     * @throws RuntimeException
     */
    public function export() : string
    {
        if (! $this->isExportable()) {
            throw new RuntimeException('Unable to export value');
        }

        return var_export($this->value, true);
    }

    /**
     * Checks wether the value can be exported for code generation or not
     *
     * @return bool
     */
    public function isExportable() : bool
    {
        if (is_scalar($this->value) || ($this->value === null)) {
            return true;
        }

        if (is_object($this->value) && method_exists($this->value, '__set_state')) {
            $reflection = new ReflectionObject($this->value);
            $method = $reflection->getMethod('__set_state');

            return ($method->isStatic() && $method->isPublic());
        }

        return false;
    }
}
