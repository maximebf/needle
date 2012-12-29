<?php

namespace Needle\Tests;

use Needle;

class NeedleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $n = new Needle();
        $n['Service'] = function($n) { return new Service(); };
        $n['Object'] = $n->inject('Needle\Tests\TestObject');
        $n['ObjectFactory'] = $n->factory('Needle\Tests\TestObject');
        $this->needle = $n;
    }

    public function testInject()
    {
        $obj = $this->needle['Object'];
        $this->assertInstanceOf('Needle\Tests\TestObject', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }

    public function testFactory()
    {
        $Object = $this->needle['ObjectFactory'];
        $this->assertInstanceOf('Closure', $Object);

        $obj = $Object('foobar');
        $this->assertEquals('foobar', $obj->constructorArg);
        $this->assertInstanceOf('Needle\Tests\TestObject', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }

    public function testInvoke()
    {
        $n = $this->needle;
        $obj = $n('ObjectFactory', array('foobar'));
        $this->assertEquals('foobar', $obj->constructorArg);
        $this->assertInstanceOf('Needle\Tests\TestObject', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }
}
