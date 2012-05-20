<?php

namespace Xi\Tests\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use DateTime;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;
use Xi\Filelib\File\Resource;

class TimeDirectoryIdCalculatorTest extends \PHPUnit_Framework_TestCase
{

    protected $resource;

    protected $calc;

    protected function setUp()
    {
        $this->calc = new TimeDirectoryIdCalculator();
        $this->resource = new Resource();
    }

    /**
     * @test
     *
     */
    public function differentFormatsShouldReturnCorrectResults()
    {
        $this->calc->setFormat('Y/m/d');

        $this->resource->setDateCreated(new DateTime('1980-01-01'));
        $this->assertEquals("1980/01/01", $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setDateCreated(new DateTime('2030-11-11 10:03:35'));
        $this->assertEquals("2030/11/11", $this->calc->calculateDirectoryId($this->resource));

        $this->calc->setFormat('m/d/Y/H/i/s');
        $this->assertEquals("11/11/2030/10/03/35", $this->calc->calculateDirectoryId($this->resource));

    }

    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     *
     */
    public function unsetDateCreatedShouldThrowException()
    {
        $this->calc->calculateDirectoryId($this->resource);
    }

}
