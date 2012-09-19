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
    public function __construct(Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Injects values from the container into the object
     * 
     * @param object $object
     * @param array $properties
     * @return object
     */
    public function inject($object, $properties = null)
    {
        if ($properties === null) {
            $class = new ReflectionClass($object);
            $classname = $class->getName();
            $properties = array();
            foreach ($class->getProperties() as $prop) {
                if (!preg_match('/@inject(\s+[a-zA-Z0-9_\\\\]+|)/', $prop->getDocComment(), $matches)) {
                    continue;
                }
                $propname = $prop->getName();
                $type = trim($matches[1]);
                if (empty($type)) {
                    if (!preg_match('/@var\s+([a-zA-Z0-9_\\\\]+)/', $prop->getDocComment(), $varMatches)) {
                        throw new Exception(sprintf("Missing @var annotation for '%s::$%s'", 
                            $classname, $propname));
                    }
                    $type = $varMatches[1];
                }
                if (!$prop->isPublic()) {
                    $methodName = 'set' . ucfirst($propname);
                    if (!$class->hasMethod($methodName) || !$class->getMethod($methodName)->isPublic()) {
                        throw new Exception(sprintf("'%s::$%s' is not public and %s::%s() does not exist", 
                            $classname, $propname, $classname, $methodName));
                    }
                    $propname = $methodName;
                }
                $properties[$propname] = $type;
            }
        }

        foreach ($properties as $k => $v) {
            if (property_exists($object, $k)) {
                $object->$k = $this->container[$v];
            } else if (method_exists($object, $k)) {
                $object->$k($this->container[$v]);
            }
        }

        return $object;
    }
}
