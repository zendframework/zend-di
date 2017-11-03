<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\Resolver;

use Zend\Di\Definition\DefinitionInterface;
use Zend\Di\Exception;
use Zend\Di\ConfigInterface;
use Psr\Container\ContainerInterface;


/**
 * The default resolver implementation
 */
class DependencyResolver implements DependencyResolverInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var DefinitionInterface
     */
    protected $definition;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @var string[]
     */
    private $builtinTypes = [
        'string', 'int', 'bool', 'float', 'double', 'array', 'resource', 'callable'
    ];

    /**
     * @param DefinitionInterface $definition
     * @param ConfigInterface $instanceConfig
     */
    public function __construct(DefinitionInterface $definition, ConfigInterface $config)
    {
        $this->definition = $definition;
        $this->config = $config;
    }

    /**
     * @param   string  $type
     * @return  \Zend\Di\Definition\ClassDefinitionInterface
     */
    private function getClassDefinition($type)
    {
        if ($this->config->isAlias($type)) {
            $type = $this->config->getClassForAlias($type);
        }

        return $this->definition->getClassDefinition($type);
    }

    /**
     * Returns the configured injections for the requested type
     *
     * If type is an alias it will try to fall back to the class configuration if no parameters
     * were defined for it
     *
     * @param  string $requestedType  The type name to get injections for
     * @return array                  Injections for the method indexed by the parameter name
     */
    private function getConfiguredParameters($requestedType)
    {
        $config = $this->config;
        $params = $config->getParameters($requestedType);
        $isAlias = $config->isAlias($requestedType);
        $class = $isAlias? $config->getClassForAlias($requestedType) : $requestedType;

        if ($isAlias) {
            $params = array_merge($config->getParameters($class), $params);
        }

        $definition = $this->getClassDefinition($class);

        foreach ($definition->getSupertypes() as $supertype) {
            $supertypeParams = $config->getParameters($supertype);

            if (!empty($supertypeParams)) {
                $params = array_merge($supertypeParams, $params);
            }
        }

        // A type configuration may define a parameter should be auto resolved
        // even it was defined earlier
        $params = array_filter($params, function ($value) {
            return ($value != '*');
        });

        return $params;
    }

    /**
     * Check if $type satisfies $requiredType
     *
     * @param  string $type          The type to check
     * @param  string $requiredType  The required type to check against
     * @return bool
     */
    private function isTypeOf($type, $requiredType)
    {
        if ($this->config->isAlias($type)) {
            $type = $this->config->getClassForAlias($type);
        }

        if ($type == $requiredType) {
            return true;
        }

        if (interface_exists($type) && interface_exists($requiredType)) {
            $reflection = new \ReflectionClass($type);
            return in_array($requiredType, $reflection->getInterfaceNames());
        }

        if (!$this->definition->hasClass($type)) {
            return false;
        }

        $definition = $this->definition->getClassDefinition($type);
        return in_array($requiredType, $definition->getSupertypes()) || in_array($requiredType, $definition->getInterfaces());
    }

    /**
     * @param string $type
     * @param string $requiredType
     * @return boolean
     */
    private function isUsableType($type, $requiredType)
    {
        return ($this->isTypeOf($type, $requiredType) && (!$this->container || $this->container->has($type)));
    }

    /**
     * Check if the given value sadisfies the given type
     *
     * @param  mixed  $value  The value to check
     * @param  string $type   The typename to check against
     * @return bool
     */
    private function isValueOf($value, $type)
    {
        if (!$this->isBuiltinType($type)) {
            return ($value instanceof $type);
        }

        if ($type == 'callable') {
            return is_callable($value);
        }

        if ($type == 'iterable') {
            return (is_array($value) || ($value instanceof \Traversable));
        }

        return ($type == gettype($value));
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isBuiltinType($type)
    {
        return in_array($type, $this->builtinTypes);
    }

    /**
     * @see \Zend\Di\Resolver\DependencyResolverInterface::setContainer()
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Prepare a candidate for injection
     *
     * If the candidate is usable, its injection representation is returned
     *
     * @param   mixed   $value
     * @param   string  $requiredType
     * @return  null|TypeInjection|ValueInjection
     */
    private function prepareInjection($value, $requiredType)
    {
        if (($value instanceof ValueInjection) || ($value instanceof TypeInjection)) {
            return $value;
        }

        if (!$requiredType) {
            $isAvailableInContainer = (is_string($value) && $this->container && $this->container->has($value));
            return $isAvailableInContainer? new TypeInjection($value) : new ValueInjection($value);
        }

        if (is_string($value) && ($requiredType != 'string')) {
            return $this->isUsableType($value, $requiredType)? new TypeInjection($value) : null;
        }

        return $this->isValueOf($value, $requiredType)? new ValueInjection($value) : null;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Zend\Di\Resolver\DependencyResolverInterface::resolveParameters()
     * @param string $requestedType
     * @param array $callTimeParameters
     * @throws \Zend\Di\Exception\UnexpectedValueException
     * @throws \Zend\Di\Exception\MissingPropertyException
     * @return AbstractInjection[]
     */
    public function resolveParameters($requestedType, array $callTimeParameters = [])
    {
        $definition = $this->getClassDefinition($requestedType);
        $params = $definition->getParameters();
        $result = [];

        if (empty($params)) {
            return $result;
        }

        $configuredParameters = $this->getConfiguredParameters($requestedType);

        foreach ($params as $paramInfo) {
            $name = $paramInfo->getName();
            $type = $paramInfo->getType();

            if (isset($callTimeParameters[$name])) {
                $result[$name] = new ValueInjection($callTimeParameters[$name]);
                continue;
            }

            if (isset($configuredParameters[$name]) && ($configuredParameters[$name] !== '*')) {
                $injection = $this->prepareInjection($configuredParameters[$name], $type);

                if (!$injection) {
                    throw new Exception\UnexpectedValueException('Unusable configured injection for parameter "' . $name . '" of type "' . $type . '"');
                }

                $result[$name] = $injection;
                continue;
            }

            if ($type && !$paramInfo->isBuiltin()) {
                $preference = $this->resolvePreference($type, $requestedType);

                if ($preference) {
                    $result[$name] = new TypeInjection($preference);
                    continue;
                }

                if (($type === ContainerInterface::class) || !$this->container || $this->container->has($type)) {
                    $result[$name] = new TypeInjection($type);
                    continue;
                }
            }

            // The parameter is required, but we can't find anything that is suitable
            if ($paramInfo->isRequired()) {
                $isAlias = $this->config->isAlias($requestedType);
                $class = $isAlias? $this->config->getClassForAlias($requestedType) : $requestedType;
                throw new Exception\MissingPropertyException(sprintf('Could not resolve value for parameter "%s" of type %s in class %s (requested as %s)', $name, $type? : 'any', $class, $requestedType));
            }

            $result[$name] = new ValueInjection($paramInfo->getDefault());
        }

        foreach ($result as $name => $injection) {
            $injection->setParameterName($name);
        }

        return $result;
    }

    /**
     * @see \Zend\Di\Resolver\DependencyResolverInterface::resolvePreference()
     */
    public function resolvePreference($type, $context = null)
    {
        if ($context) {
            $preference = $this->config->getTypePreference($type, $context);

            if ($preference && $this->isUsableType($preference, $type)) {
                return $preference;
            }

            $definition = $this->getClassDefinition($context);

            foreach ($definition->getSupertypes() as $supertype) {
                $preference = $this->config->getTypePreference($type, $supertype);

                if ($preference && $this->isUsableType($preference, $type)) {
                    return $preference;
                }
            }

            foreach ($definition->getInterfaces() as $interface) {
                $preference = $this->config->getTypePreference($type, $interface);

                if ($preference && $this->isUsableType($preference, $type)) {
                    return $preference;
                }
            }
        }

        $preference = $this->config->getTypePreference($type);

        if (!$preference || !$this->isUsableType($preference, $type)) {
            $preference = null;
        }

        return $preference;
    }
}
