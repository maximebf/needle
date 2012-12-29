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
 * Needle subclasses Pimple to add more ways to create services
 */
class Needle extends Pimple
{
    /**
     * Creates a new instance of $classname and injects values 
     * into its properties using {@see NeedleInjector}
     * 
     * @param string $classname
     * @param array $properties
     * @param bool $allowUndefinedProperties
     * @param bool $useSetters
     * @return Closure
     */
    public function inject($classname, $properties = null, $allowUndefinedProperties = false, $useSetters = false)
    {
        return function($p) use ($classname, $properties, $allowUndefinedProperties, $useSetters) {
            $injector = new NeedleInjector($p);
            return $injector->inject(new $classname(), $properties, $allowUndefinedProperties, $useSetters);
        };
    }

    /**
     * Returns a service which returns a Closure which can be used 
     * in place of object initialization and provides injection
     * of created objects (using NeedleInjector)
     * 
     * <code>
     * class User { public function __construct($name) {} }
     * $needle['User'] = $needle->factory('User');
     * $User = $needle['User'];
     * $foo = $User('foo');
     * assert($user instanceof User);
     * </code>
     * 
     * @param string $classname
     * @param array $properties
     * @param bool $allowUndefinedProperties
     * @param bool $useSetters
     * @return Closure
     */
    public function factory($classname, $properties = null, $allowUndefinedProperties = false, $useSetters = false)
    {
        return function($p) use ($classname, $properties, $allowUndefinedProperties, $useSetters) {
            return function() use ($p, $classname, $properties, $allowUndefinedProperties, $useSetters) {
                $class = new ReflectionClass($classname);
                $injector = new NeedleInjector($p);
                return $injector->inject($class->newInstanceArgs(func_get_args()), 
                    $properties, $allowUndefinedProperties, $useSetters);
            };
        };
    }

    /**
     * Invokes a factory
     *
     * <code>
     * $needle['User'] = $needle->factory('User');
     * $user = $needle('User');
     * assert($user instanceof User);
     * </code>
     * 
     * @param string $id
     * @return object
     */
    public function __invoke($id, $args = array())
    {
        return call_user_func_array($this[$id], $args);
    }
}
