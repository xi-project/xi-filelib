<?php

namespace Xi\Tests\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\LeveledDirectoryIdCalculator,
    Xi\Filelib\File\FileItem;

class LeveledDirectoryIdCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileItem
     */
    protected $file;

    /**
     * @var LeveledDirectoryIdCalculator
     */
    protected $calc;

    protected function setUp()
    {
        $this->calc = new LeveledDirectoryIdCalculator();
        $this->file = new FileItem();
    }

    /**
     * @test
     */
    public function oneLeveled()
    {
        $this->calc->setDirectoryLevels(1);
        $this->calc->setFilesPerDirectory(10);

        $this->file->setId(1);
        $this->assertEquals('1', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(10);
        $this->assertEquals('1', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(11);
        $this->assertEquals('2', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(1000);
        $this->assertEquals('100', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(1001);
        $this->assertEquals('101', $this->calc->calculateDirectoryId($this->file));

        $this->calc->setFilesPerDirectory(77);
        $this->assertEquals('13', $this->calc->calculateDirectoryId($this->file));
    }

    /**
     * @test
     */
    public function twoLeveled()
    {
        $this->calc->setDirectoryLevels(2);
        $this->calc->setFilesPerDirectory(100);

        $this->file->setId(1);
        $this->assertEquals('1/1', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(100);
        $this->assertEquals('1/1', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(101);
        $this->assertEquals('1/2', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(100000);
        $this->assertEquals('10/100', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(100001);
        $this->assertEquals('11/1', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(123456789);
        $this->calc->setFilesPerDirectory(777);

        $this->assertEquals('205/382', $this->calc->calculateDirectoryId($this->file));
    }

    /**
     * @test
     */
    public function fiveLeveled()
    {
        $this->calc->setDirectoryLevels(5);
        $this->calc->setFilesPerDirectory(100);

        $this->file->setId(1);
        $this->assertEquals('1/1/1/1/1', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(101);
        $this->assertEquals('1/1/1/1/2', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(100001);
        $this->assertEquals('1/1/1/11/1', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(100000000);
        $this->assertEquals('1/1/100/100/100', $this->calc->calculateDirectoryId($this->file));

        $this->file->setId(100000001);
        $this->assertEquals('1/2/1/1/1', $this->calc->calculateDirectoryId($this->file));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function throwsExceptionOnNonNumericFileId()
    {
        $this->file->setId('xoo');
        $this->assertEquals('1/1/1/1/1', $this->calc->calculateDirectoryId($this->file));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function throwsExceptionWithDirectoryLevelSmallerThanOne()
    {
        $this->file->setId(1);
        $this->calc->setDirectoryLevels(-1);
        $this->calc->calculateDirectoryId($this->file);
    }
}
