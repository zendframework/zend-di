<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Di;

use ArrayAccess;

/**
 * Provides a DI configuration from an array.
 *
 * This configures the instanciation process of the dependency injector.
 *
 * **Example:**
 *
 * <code>
 * return [
 *     // This section provides global type preferences.
 *     // Those are visited if a specific instance has no preference definitions.
 *     'preferences' => [
 *         // The key is the requested class or interface name, the values are
 *         // the types the dependency injector should prefer.
 *         Some\Interface::class => Some\Preference::class
 *     ],
 *     // This configures the instanciation of specific types.
 *     // Types may also be purely virtual by defining the aliasOf key.
 *     'types' => [
 *         My\Class::class => [
 *              'preferences' => [
 *                  // this supercedes the global type preferences
 *                  // when My\Class is instanciated
 *                  Some\Interface::class => 'My.SpecificAlias'
 *              ],
 *
 *              // Instanciation paramters. These will only be used for
 *              // the instantiator (i.e. the constructor)
 *              'parameters' => [
 *                  'foo' => My\FooImpl::class, // Use the given type to provide the injection (depends on definition)
 *                  'bar' => '*' // Use the type preferences
 *              ],
 *         ],
 *
 *         'My.Alias' => [
 *             // typeOf defines virtual classes which can be used as type
 *             // preferences or for newInstance calls. They allow providing
 *             // custom configs for a class
 *             'typeOf' => Some\Class::class,
 *             'preferences' => [
 *                  Foo::class => Bar::class
 *             ]
 *         ]
 *     ]
 * ];
 * </code>
 *
 * ## Notes on Injections
 *
 * Named arguments and Automatic type lookups will only work for Methods that
 * are known to the dependency injector through its definitions. Injections for
 * unknown methods do not perform type lookups on its own.
 *
 * A value injection without any lookups can be forced by providing a
 * Resolver\ValueInjection instance.
 *
 * To force a service/class instance provide a Resolver\TypeInjection instance.
 * For classes known from the definitions, a type preference might be the
 * better approach
 *
 * @see Zend\Di\Resolver\ValueInjection A container to force injection of a value
 * @see Zend\Di\Resolver\TypeInjection  A container to force looking up a specific type instance for injection
 */
class Config implements ConfigInterface
{
    /**
     * @var array
     */
    protected $preferences = [];

    /**
     * @var array
     */
    protected $types = [];

    /**
     * Construct from option array
     *
     * Utilizes the given options array or traversable.
     *
     * @param array|ArrayAccess $options The options array. Traversables will
     *     be converted to an array internally.
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = [])
    {
        if (! is_array($options) && ! ($options instanceof ArrayAccess)) {
            throw new Exception\InvalidArgumentException(
                'Config data must be of type array or ArrayAccess'
            );
        }

        $this->preferences = $this->getDataFromArray($options, 'preferences')?: [];
        $this->types = $this->getDataFromArray($options, 'types')?: [];
    }

    /**
     * @param array $data
     * @param string $key
     * @return array|ArrayAccess|null
     */
    private function getDataFromArray($data, $key)
    {
        if (! isset($data[$key])
            || (! is_array($data[$key]) && ! ($data[$key] instanceof ArrayAccess))
        ) {
            return null;
        }

        return $data[$key];
    }

    /**
     * {@inheritDoc}
     * @see Zend\Di\ConfigInterface::getClassForAlias()
     */
    public function getClassForAlias(string $name) : ?string
    {
        if (isset($this->types[$name]['typeOf'])) {
            return $this->types[$name]['typeOf'];
        }

        return null;
    }

    /**
     * Returns the instanciation paramters for the given type
     *
     * @param string $type The alias or class name
     * @return array The configured parameters
     */
    public function getParameters(string $type) : array
    {
        if (! isset($this->types[$type]['parameters']) || ! is_array($this->types[$type]['parameters'])) {
            return [];
        }

        return $this->types[$type]['parameters'];
    }

    /**
     * {@inheritDoc}
     * @see Zend\Di\ConfigInterface::setParameters()
     */
    public function setParameters(string $type, array $params)
    {
        $this->types[$type]['parameters'] = $params;
        return $this;
    }

    /**
     * @param string $type
     * @param string $context
     * @return string|null
     */
    public function getTypePreference(string $type, ?string $context = null) : ?string
    {
        if ($context) {
            return $this->getTypePreferenceForClass($type, $context);
        }

        if (! isset($this->preferences[$type])) {
            return null;
        }

        $preference = $this->preferences[$type];
        return ($preference != '') ? (string)$preference : null;
    }

    /**
     * {@inheritDoc}
     * @see Zend\Di\ConfigInterface::getTypePreferencesForClass()
     */
    private function getTypePreferenceForClass(string $type, ?string $context) : ?string
    {
        if (! isset($this->types[$context]['preferences'][$type])) {
            return null;
        }

        $preference = $this->types[$context]['preferences'][$type];
        return ($preference != '') ? (string)$preference : null;
    }

    /**
     * {@inheritDoc}
     * @see ConfigInterface::isAlias()
     */
    public function isAlias(string $name) : bool
    {
        return isset($this->types[$name]['typeOf']);
    }

    /**
     * {@inheritDoc}
     * @see ConfigInterface::getConfiguredTypeNames()
     */
    public function getConfiguredTypeNames() : array
    {
        return array_keys($this->types);
    }

    /**
     * @param string $type
     * @param string $preference
     * @param string $context
     */
    public function setTypePreference(string $type, string $preference, ?string $context = null) : self
    {
        if ($context) {
            $this->types[$context]['preferences'][$type] = $preference;
            return $this;
        }

        $this->preferences[$type] = $preference;
        return $this;
    }

    /**
     * @param string $name The name of the alias
     * @param string $class The class name this alias points to
     * @throws Exception\ClassNotFoundException When `$class` does not exist
     * @return self
     */
    public function setAlias(string $name, string $class) : self
    {
        if (! class_exists($class) && ! interface_exists($class)) {
            throw new Exception\ClassNotFoundException('Could not find class "' . $class . '"');
        }

        $this->types[$name]['typeOf'] = $class;
        return $this;
    }
}
