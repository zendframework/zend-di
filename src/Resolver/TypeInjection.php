<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
    public function __construct($type)
    {
        $this->type = (string)$type;
    }

    /**
     * Get the type name to look up for injection
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\Resolver\AbstractInjection::export()
     */
    public function export()
    {
        return var_export($this->type, true);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\Resolver\AbstractInjection::isExportable()
     */
    public function isExportable()
    {
        return true;
    }

    /**
     * Simply converts to the type name string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->type;
    }
}
