<?php

namespace Needle\Tests;

use Needle;

class NeedleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->n = new Needle();
        $this->n['Service'] = function($n) { return new Service(); };
    }

    public function testInjectWithClassname()
    {
        $this->n['Object'] = $this->n->inject('Needle\Tests\TestObject');

        $obj = $this->n['Object'];
        $this->assertInstanceOf('Needle\Tests\TestObject', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }

    public function testInjectWithClosure()
    {
        $this->n['Object'] = $this->n->inject(function($n) { return new TestObject(); });

        $obj = $this->n['Object'];
        $this->assertInstanceOf('Needle\Tests\TestObject', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }

    public function testFactoryWithClassname()
    {
        $this->n['ObjectFactory'] = $this->n->factory('Needle\Tests\TestObject');

        $Object = $this->n['ObjectFactory'];
        $this->assertInstanceOf('Closure', $Object);

        $obj = $Object('foobar');
        $this->assertEquals('foobar', $obj->constructorArg);
        $this->assertInstanceOf('Needle\Tests\TestObject', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }

    public function testFactoryWithClosure()
    {
        $this->n['ObjectFactory'] = $this->n->factory(function($n, $args) {
            return new TestObject('hello world');
        });

        $Object = $this->n['ObjectFactory'];
        $this->assertInstanceOf('Closure', $Object);

        $obj = $Object('foobar');
        $this->assertEquals('hello world', $obj->constructorArg);
        $this->assertInstanceOf('Needle\Tests\TestObject', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }

    public function testInvoke()
    {
        $this->n['ObjectFactory'] = $this->n->factory('Needle\Tests\TestObject');
        
        $n = $this->n;
        $obj = $n('ObjectFactory', array('foobar'));
        $this->assertEquals('foobar', $obj->constructorArg);
        $this->assertInstanceOf('Needle\Tests\TestObject', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }
}
