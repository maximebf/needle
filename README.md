
# Needle

Annotations based injection on top of [Pimple](https://github.com/fabpot/Pimple)

[![Build Status](https://secure.travis-ci.org/maximebf/needle.png)](http://travis-ci.org/maximebf/needle)

## Installation

The easiest way to install Needle is using [Composer](https://github.com/composer/composer)
with the following requirement (Pimple will be automatically installed):

    {
        "require": {
            "maximebf/needle": ">=0.1.0"
        }
    }

Alternatively, you can [download the archive](https://github.com/maximebf/needle/zipball/master) 
and add the src/ folder to PHP's include path:

    set_include_path('/path/to/src' . PATH_SEPARATOR . get_include_path());

Needle does not provide an autoloader but follows the [PSR-0 convention](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).  
You can use the following snippet to autoload Needle classes:

    spl_autoload_register(function($className) {
        if (substr($className, 0, 6) === 'Needle') {
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
            require_once $filename;
        }
    });

(You will also need to install and autoload Pimple classes)

## Basic usage

Needle injects services from a Pimple container into properties of an object
according to annotations.

    class Permissions {}

    class User
    {
        /**
         * @inject
         * @var Permissions
         */
        public $permissions;
    }

    $n = new Needle();
    $n['Permissions'] = function($n) { return new Permissions(); };
    $n['User'] = $n->inject('User');

    $user = $n['User'];
    assert(!empty($user->permissions));

The `inject()` function takes as argument either a class name like above or
a closure like any other Pimple service:

    $n['User'] = $n->inject(function($n) { return new User(); });

## Annotations

There are two ways to specify how a property can be injected:

 - using the `@inject` and `@var` annotations. The `@var` annotation must
   specify a Pimple service name
 - using only `@inject` followed by a service name (this will overtake `@var`
   if it's also present)

## Using Needle without annotations

It's not mandatory to use annotations to define which properties will
be injected. `@inject()` can take as second argument an array of key/value
pairs where keys are property names and values are service names.

    class Permissions {}
    class User { public $permissions; }

    $n = new Needle();
    $n['Permissions'] = function($n) { return new Permissions(); };
    $n['User'] = $n->inject('User', array('permissions' => 'Permissions'));

Properties will be injected according to their annotations and the properties array.
The latter overtake any annotations. Set a value to `null` to prevent the injection.

## Injection mechanism

In the default use case, the property value will be directly set with the 
service object. The property must exist before it can be injected.

To inject properties which are not defined (thus creating them on the fly),
use `true` as the third argument to `@inject`:

    // ...
    class User {}
    // ...
    $n['User'] = $n->inject('User', array('permissions' => 'Permissions'), true);

Needle can also use a setter method (if it exists) when `true` is used as the
fourth argument:

    // ...
    class User
    {
        /** @inject Permissions */
        protected $permissions;

        protected $modified = false;

        public function setPermissions(Permissions $p)
        {
            $this->permissions = $p;
            $this->modified = true;
        }

        public function getPermissions()
        {
            return $this->permissions;
        }
    }
    // ...
    $n['User'] = $n->inject('User', array(), false, true);

## Factories

Needle also introduces the concept of factories to create closures which return 
injected objects.

    class Permissions {}

    class User
    {
        /**
         * @inject
         * @var Permissions
         */
        public $permissions;

        public $name;

        public function __construct($name)
        {
            $this->name = $name;
        }
    }

    $n = new Needle();
    $n['Permissions'] = function($n) { return new Permissions(); };
    $n['UserFactory'] = $n->factory('User');

    $User = $n['UserFactory'];

    $foo = $User('foo');
    $bar = $User('bar');

In this example, a factory is created for the ̀User` class.
`$n['UserFactory']` returns a closure which can be used to create
injected `User` objects. Arguments passed to the closure will be forwarded
to the class' constructor.

Factories can also be used using closures instead of class names. The closure
has two parameters: the container and an array of arguments.

    $n['UserFactory'] = $n->factory(function($n, $args) {
        return new User($args[0]);
    });

Instead of first getting the closure using `$n['UserFactory']` you can
invoke the Needle object with the name of the service as first argument
and an array of arguments as the second:

    $foo = $n('UserFactory', array('foo'));
    $bar = $n('UserFactory', array('bar'));
