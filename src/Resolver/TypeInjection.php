<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Resolver;

use Psr\Container\ContainerInterface;

use function trigger_error;
use const E_USER_DEPRECATED;

/**
 * Wrapper for types that should be looked up for injection
 */
final class TypeInjection implements InjectionInterface
{
    /**
     * Holds the type name to look up
     *
     * @var string
     */
    private $type;

    /**
     * Constructor
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function export() : string
    {
        return var_export($this->type, true);
    }

    public function isExportable() : bool
    {
        return true;
    }

    public function toValue(ContainerInterface $container)
    {
        return $container->get($this->type);
    }

    /**
     * Reflects the type name
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->type;
    }

    /**
     * Get the type name to look up for injection
     *
     * @codeCoverageIgnore
     * @deprecated Since 3.1.0
     * @see toValue()
     * @see export()
     * @see __toString()
     * @return string
     */
    public function getType() : string
    {
        trigger_error(__METHOD__ . ' is deprecated. Please migrate to __toString()', E_USER_DEPRECATED);
        return $this->type;
    }
}
