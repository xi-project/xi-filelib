<?php

namespace Xi\Tests\Filelib\Backend\Finder;

use Xi\Tests\Filelib\TestCase as XiTestCase;
use Xi\Filelib\Backend\Finder\Finder;

abstract class TestCase extends XiTestCase
{
    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @abstract
     * @return array
     */
    abstract public function getExpectedFields();

    /**
     * @abstract
     * @return string
     */
    abstract public function getExpectedResultClass();

    /**
     * @test
     */
    public function getFieldsShouldReturnCorrectFields()
    {
        $expected = $this->getExpectedFields();
        $this->assertEquals($expected, $this->finder->getFields());
    }

    /**
     * @test
     */
    public function getResultClassShouldReturnCorrectClassName()
    {
        $expected = $this->getExpectedResultClass();
        $this->assertEquals($expected, $this->finder->getResultClass());
    }

    /**
     * @test
     */
    public function allowedParametersShouldBeSettableViaConstructor()
    {
        $class = get_class($this->finder);

        $params = array();
        foreach ($this->getExpectedFields() as $name) {
            $params[$name] = 'luss';
        }

        $obj = new $class($params);

        $this->assertEquals($params, $obj->getParameters());
    }
}
