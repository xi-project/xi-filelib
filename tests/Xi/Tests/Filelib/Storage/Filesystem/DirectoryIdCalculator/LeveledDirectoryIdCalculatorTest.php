<?php

namespace Xi\Tests\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\LeveledDirectoryIdCalculator;
use Xi\Filelib\File\Resource;

class LeveledDirectoryIdCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var LeveledDirectoryIdCalculator
     */
    protected $calc;

    protected function setUp()
    {
        $this->calc = new LeveledDirectoryIdCalculator();
        $this->resource = new Resource();
    }

    /**
     * @test
     */
    public function oneLeveled()
    {
        $this->calc->setDirectoryLevels(1);
        $this->calc->setFilesPerDirectory(10);

        $this->resource->setId(1);
        $this->assertEquals('1', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(10);
        $this->assertEquals('1', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(11);
        $this->assertEquals('2', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(1000);
        $this->assertEquals('100', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(1001);
        $this->assertEquals('101', $this->calc->calculateDirectoryId($this->resource));

        $this->calc->setFilesPerDirectory(77);
        $this->assertEquals('13', $this->calc->calculateDirectoryId($this->resource));
    }

    /**
     * @test
     */
    public function twoLeveled()
    {
        $this->calc->setDirectoryLevels(2);
        $this->calc->setFilesPerDirectory(100);

        $this->resource->setId(1);
        $this->assertEquals('1/1', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(100);
        $this->assertEquals('1/1', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(101);
        $this->assertEquals('1/2', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(100000);
        $this->assertEquals('10/100', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(100001);
        $this->assertEquals('11/1', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(123456789);
        $this->calc->setFilesPerDirectory(777);

        $this->assertEquals('205/382', $this->calc->calculateDirectoryId($this->resource));
    }

    /**
     * @test
     */
    public function fiveLeveled()
    {
        $this->calc->setDirectoryLevels(5);
        $this->calc->setFilesPerDirectory(100);

        $this->resource->setId(1);
        $this->assertEquals('1/1/1/1/1', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(101);
        $this->assertEquals('1/1/1/1/2', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(100001);
        $this->assertEquals('1/1/1/11/1', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(100000000);
        $this->assertEquals('1/1/100/100/100', $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setId(100000001);
        $this->assertEquals('1/2/1/1/1', $this->calc->calculateDirectoryId($this->resource));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function throwsExceptionOnNonNumericFileId()
    {
        $this->resource->setId('xoo');
        $this->assertEquals('1/1/1/1/1', $this->calc->calculateDirectoryId($this->resource));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function throwsExceptionWithDirectoryLevelSmallerThanOne()
    {
        $this->resource->setId(1);
        $this->calc->setDirectoryLevels(-1);
        $this->calc->calculateDirectoryId($this->resource);
    }
}
