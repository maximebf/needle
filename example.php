<?php

include __DIR__ . '/tests/bootstrap.php';

class Permissions
{
    public $allowed = true;
}

class User
{
    public $name;

    /**
     * @inject
     * @var Permissions
     */
    protected $permissions;

    public function __construct($name = null)
    {
        $this->name = $name;
    }

    public function setPermissions(Permissions $p)
    {
        $this->permissions = $p;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }
}

$needle = new Needle();
$needle['Permissions'] = function($p) { return new Permissions(); };
$needle['User'] = $needle->inject('User');
$needle['UserFactory'] = $needle->factory('User');

$foo = $needle['User'];
var_dump($foo);

$User = $needle['UserFactory'];
$bar = $User('bar');
var_dump($bar);

$baz = $needle('UserFactory', array('baz'));
var_dump($baz);
