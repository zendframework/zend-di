<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Definition;

/**
 * Parameter definition
 */
interface ParameterInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @return mixed
     */
    public function getDefault();

    /**
     * @return bool
     */
    public function isRequired();

    /**
     * @return bool
     */
    public function isBuiltin();
}
