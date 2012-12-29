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
     * @param string $classnameOrClosure
     * @param array $properties
     * @param bool $allowUndefinedProperties
     * @param bool $useSetters
     * @return Closure
     */
    public function inject($classnameOrClosure, $properties = array(), $allowUndefinedProperties = false, $useSetters = false)
    {
        return function($p) use ($classnameOrClosure, $properties, $allowUndefinedProperties, $useSetters) {
            if ($classnameOrClosure instanceof \Closure) {
                $object = $classnameOrClosure($p);
            } else {
                $object = new $classnameOrClosure();
            }
            $injector = new NeedleInjector($p);
            return $injector->inject($object, $properties, $allowUndefinedProperties, $useSetters);
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
     * @param string $classnameOrClosure
     * @param array $properties
     * @param bool $allowUndefinedProperties
     * @param bool $useSetters
     * @return Closure
     */
    public function factory($classnameOrClosure, $properties = array(), $allowUndefinedProperties = false, $useSetters = false)
    {
        return function($p) use ($classnameOrClosure, $properties, $allowUndefinedProperties, $useSetters) {
            return function() use ($p, $classnameOrClosure, $properties, $allowUndefinedProperties, $useSetters) {
                $args = func_get_args();
                if ($classnameOrClosure instanceof \Closure) {
                    $object = $classnameOrClosure($p, $args);
                } else {
                    $class = new ReflectionClass($classnameOrClosure);
                    $object = $class->newInstanceArgs($args);
                }
                $injector = new NeedleInjector($p);
                return $injector->inject($object, $properties, $allowUndefinedProperties, $useSetters);
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
