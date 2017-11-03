<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

/**
 * Provides a DI configuration from an array
 *
 * This configures the instanciation process of the dependency injector
 *
 * **Example:**
 * ```php
 * return [
 *     // This section provides global type preferences
 *     // Those are visited if a specific instance has no preference definions
 *     'preferences' => [
 *         // The key is the requested class or interface name, the values are
 *         // the types the dependency injector should prefer
 *         Some\Interface::class => Some\Preference::class
 *     ],
 *     // This configures the instanciation of specific types
 *     // Types may also be purely virtual by defining the aliasOf key.
 *     'types' => [
 *         My\Class::class => [
 *              'preferences' => [
 *                  // this superseds the global type preferences
 *                  // when My\Class is instanciated
 *                  Some\Interface::class => 'My.SpecificAlias'
 *              ],
 *
 *              // Instanciation paramters. These will only be used for
 *              // the instanciator (i.e. the constructor)
 *              'parameters' => [
 *                  'foo' => My\FooImpl::class, // Use the given type to provide the injection (depends on definition)
 *                  'bar' => '*' // Use the type preferences
 *              ],
 *         ],
 *
 *         'My.Alias' => [
 *             // typeOf defines virtual classes which can be used as type perferences or for
 *             // newInstance calls. They allow providing a different configs for a class
 *             'typeOf' => Some\Class::class,
 *             'preferences' => [
 *                  Foo::class => Bar::class
 *             ]
 *         ]
 *     ]
 * ];
 * ```
 *
 * ## Notes on Injections
 *
 * Named arguments and Automatic type lookups will only work for Methods that are known to the dependency injector
 * through its definitions. Injections for unknown methods do not perform type lookups on its own.
 *
 * A value injection without any lookups can be forced by providing a Resolver\ValueInjection instance.
 *
 * To force a service/class instance provide a Resolver\TypeInjection instance. For classes known from
 * the definitions, a type preference might be the better approach
 *
 * @see Zend\Di\Resolver\ValueInjection A container to force injection of a value
 * @see Zend\Di\Resolver\TypeInjection  A container to force looking up a specific type instance for injection
 */
class Config implements ConfigInterface
{
    /**
     * @var array
     */
    private $preferences = [];

    /**
     * @var array
     */
    private $types = [];

    /**
     * Construct from option array
     *
     * Utilizes the given options array or traversable.
     *
     * @param  array|\ArrayAccess   $options    The options array. Traversables
     *                                          will be converted to an array
     *                                          internally
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = [])
    {
        if (!is_array($options) && !($options instanceof \ArrayAccess)) {
            throw new Exception\InvalidArgumentException(
                'Config data must be of type array or array access'
            );
        }

        $this->preferences = $this->getDataFromArray($options, 'preferences')?: [];
        $this->types = $this->getDataFromArray($options, 'types')?: [];
    }

    /**
     * @param array $data
     * @param string $key
     * @return array|\ArrayAccess|null
     */
    private function getDataFromArray($data, $key)
    {
        if (!isset($data[$key]) || (!is_array($data[$key]) && !($data[$key] instanceof \ArrayAccess))) {
            return null;
        }

        return $data[$key];
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\ConfigInterface::getClassForAlias()
     */
    public function getClassForAlias($name)
    {
        if (isset($this->types[$name]['typeOf'])) {
            return $this->types[$name]['typeOf'];
        }

        return null;
    }

    /**
     * Returns the instanciation paramters for the given type
     *
     * @param   string  $type   The alias or class name
     * @return  array           The configured parameters
     */
    public function getParameters($type)
    {
        if (!isset($this->types[$type]['parameters']) || !is_array($this->types[$type]['parameters'])) {
            return [];
        }

        return $this->types[$type]['parameters'];
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\ConfigInterface::setParameters()
     */
    public function setParameters($type, array $params)
    {
        $this->types[$type]['parameters'] = $params;
        return $this;
    }

    /**
     * @param string $type
     * @param string $context
     * @return string|null
     */
    public function getTypePreference($type, $context = null)
    {
        if ($context) {
            return $this->getTypePreferenceForClass($type, $context);
        }

        if (!isset($this->preferences[$type])) {
            return null;
        }

        $preference = $this->preferences[$type];
        return ($preference != '')? (string)$preference : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\ConfigInterface::getTypePreferencesForClass()
     */
    private function getTypePreferenceForClass($type, $context)
    {
        if (!isset($this->types[$context]['preferences'][$type])) {
            return null;
        }

        $preference = $this->types[$context]['preferences'][$type];
        return ($preference != '')? (string)$preference : null;
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\ConfigInterface::isAlias()
     */
    public function isAlias($name)
    {
        return isset($this->types[$name]['typeOf']);
    }

    /**
     * {@inheritDoc}
     * @see \Zend\Di\ConfigInterface::getConfiguredTypeNames()
     */
    public function getConfiguredTypeNames()
    {
        return array_keys($this->types);
    }
}
