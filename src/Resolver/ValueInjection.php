<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Di\Resolver;

use Psr\Container\ContainerInterface;
use ReflectionObject;
use Zend\Di\Exception\LogicException;

use function trigger_error;
use const E_USER_DEPRECATED;

/**
 * Wrapper for values that should be directly injected
 */
class ValueInjection implements InjectionInterface
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
     * @param array $state
     */
    public static function __set_state(array $state) : self
    {
        return new self($state['value']);
    }

    /**
     * Exports the encapsulated value to php code
     *
     * @return string
     * @throws LogicException
     */
    public function export() : string
    {
        if (! $this->isExportable()) {
            throw new LogicException('Unable to export value');
        }

        if ($this->value === null) {
            return 'null';
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

    public function toValue(ContainerInterface $container)
    {
        return $this->value;
    }

    /**
     * Get the value to inject
     *
     * @deprecated Since 3.1.0
     * @see toValue()
     */
    public function getValue()
    {
        trigger_error(
            __METHOD__ . ' is deprecated, please migrate to ' . __CLASS__ . '::toValue().',
            E_USER_DEPRECATED
        );

        return $this->value;
    }
}
