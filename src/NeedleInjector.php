<?php
/*
 * This file is part of the Needle package.
 *
 * (c) 2012 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Injects values from the container into an object
 */
class NeedleInjector
{
    /** @var Pimple */
    protected $container;

    /**
     * @param Pimple $container
     */
    public function __construct(Pimple $container = null)
    {
        $this->container = $container;
    }

    /**
     * Sets the container from which injected values will be taken
     * 
     * @param Pimple $container
     */
    public function setContainer(Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the container from which injected values are taken
     * 
     * @return Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Injects values from the container into the object
     * 
     * @param object $object
     * @param array $properties An array where keys are property names and values the Pimple service name
     * @param bool $allowUndefinedProperties Whether to allow injecting of unknown properties
     * @param bool $useSetters Whether to use setter methods instead of directly setting the property value
     * @return object
     */
    public function inject($object, $properties = array(), $allowUndefinedProperties = false, $useSetters = false)
    {
        $propertiesFromAnnotations = $this->extractPropertiesFromAnnotations($object);
        $properties = array_filter(array_merge($propertiesFromAnnotations, $properties));

        $class = new ReflectionClass($object);
        foreach ($properties as $propname => $service) {
            if (!$class->hasProperty($propname)) {
                if ($allowUndefinedProperties) {
                    $object->$propname = $this->container[$service];
                }
                continue;
            }

            $prop = $class->getProperty($propname);
            $value = $this->container[$service];

            if (!$prop->isPublic()) {
                $methodName = 'set' . ucfirst($propname);
                if ($useSetters && $class->hasMethod($methodName)) {
                    $class->getMethod($methodName)->invoke($object, $value);
                } else {
                    $prop->setAccessible(true);
                    $prop->setValue($object, $value);
                    $prop->setAccessible(false);
                }
            } else {
                $prop->setValue($object, $value);
            }
        }

        return $object;
    }

    /**
     * Extracts which properties to inject using annotations
     * 
     * @param object $object
     * @return array
     */
    public function extractPropertiesFromAnnotations($object)
    {
        $class = new ReflectionClass($object);
        $classname = $class->getName();
        $properties = array();

        foreach ($class->getProperties() as $prop) {
            if (!preg_match('/@inject(\s+[a-zA-Z0-9_\\\\]+|)/', $prop->getDocComment(), $matches)) {
                continue;
            }
            $propname = $prop->getName();
            $service = trim($matches[1]);
            if (empty($service)) {
                if (!preg_match('/@var\s+([a-zA-Z0-9_\\\\]+)/', $prop->getDocComment(), $varMatches)) {
                    throw new Exception(sprintf("Missing @var annotation for '%s::$%s'", 
                        $classname, $propname));
                }
                $service = $varMatches[1];
            }
            $properties[$propname] = $service;
        }

        return $properties;
    }
}
