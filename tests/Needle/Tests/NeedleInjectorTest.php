<?php

namespace Needle\Tests;

use Pimple;
use NeedleInjector;

class NeedleInjectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pimple = new Pimple();
        $this->pimple['Service'] = function($p) { return new Service(); };
    }

    public function testExtractAnnotations()
    {
        $injector = new NeedleInjector($this->pimple);
        $props = $injector->extractPropertiesFromAnnotations(new TestObject());

        $this->assertArrayHasKey('service', $props);
        $this->assertEquals('Service', $props['service']);
        $this->assertArrayHasKey('service2', $props);
        $this->assertEquals('Service', $props['service2']);
        $this->assertArrayNotHasKey('service3', $props);
    }

    public function testInjectWithPropertiesArray()
    {
        $props = array('service2' => null, 'service3' => 'Service');
        $obj = new TestObject();

        $injector = new NeedleInjector($this->pimple);
        $injector->inject($obj, $props);

        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
        $this->assertNull($obj->getService2());
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService3());
        $this->assertFalse($obj->setterCalled);
    }

    public function testInjectThroughSetter()
    {
        $obj = new TestObject();
        $injector = new NeedleInjector($this->pimple);
        $injector->inject($obj, array(), true, true);

        $this->assertTrue($obj->setterCalled);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->getService());
    }

    public function testInjectUndefinedPropertiesArray()
    {
        $props = array('service' => 'Service');
        $injector = new NeedleInjector($this->pimple);

        $obj = new \stdClass();
        $injector->inject($obj, $props);
        $this->assertObjectNotHasAttribute('service', $obj);

        $obj = new \stdClass();
        $injector->inject($obj, $props, true);
        $this->assertObjectHasAttribute('service', $obj);
        $this->assertInstanceOf('Needle\Tests\Service', $obj->service);
    }
}
