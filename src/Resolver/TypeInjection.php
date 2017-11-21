<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di\Resolver;

/**
 * Wrapper for types that should be looked up for injection
 */
final class TypeInjection extends AbstractInjection
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

    /**
     * Get the type name to look up for injection
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     * @see AbstractInjection::export()
     */
    public function export() : string
    {
        return var_export($this->type, true);
    }

    /**
     * {@inheritDoc}
     * @see AbstractInjection::isExportable()
     */
    public function isExportable() : bool
    {
        return true;
    }

    /**
     * Simply converts to the type name string
     *
     * @codeCoverageIgnore Too trivial to require a test
     * @return string
     */
    public function __toString() : string
    {
        return $this->type;
    }
}
