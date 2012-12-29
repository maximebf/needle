<?php

namespace Needle\Tests;

class TestObject
{
    /**
     * @inject Service
     */
    protected $service;

    /**
     * @inject
     * @var Service
     */
    protected $service2;

    protected $service3;

    public $constructorArg;

    public $setterCalled = false;

    public function __construct($constructorArg = null)
    {
        $this->constructorArg = $constructorArg;
    }

    public function setService(Service $service)
    {
        $this->service = $service;
        $this->setterCalled = true;
    }

    public function getService()
    {
        return $this->service;
    }

    public function getService2()
    {
        return $this->service2;
    }

    public function getService3()
    {
        return $this->service3;
    }
}
