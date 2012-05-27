<?php

namespace Xi\Tests\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use \DateTime;

/**
 * @group storage
 */
class TimeDirectoryIdCalculatorTest extends \PHPUnit_Framework_TestCase
{
    
    protected $file;
    
    protected $calc;
    
    protected function setUp()
    {
        $this->calc = new \Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator();
        $this->file = new \Xi\Filelib\File\File();
    }
    
    /**
     * @test
     * 
     */
    public function differentFormatsShouldReturnCorrectResults()
    {
        $this->calc->setFormat('Y/m/d');

        $this->file->setDateUploaded(new DateTime('1980-01-01'));
        $this->assertEquals("1980/01/01", $this->calc->calculateDirectoryId($this->file));

        $this->file->setDateUploaded(new DateTime('2030-11-11 10:03:35'));
        $this->assertEquals("2030/11/11", $this->calc->calculateDirectoryId($this->file));
                
        $this->calc->setFormat('m/d/Y/H/i/s');
        $this->assertEquals("11/11/2030/10/03/35", $this->calc->calculateDirectoryId($this->file));
                
    }
    
    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     * 
     */
    public function unsetDateUploadedShouldThrowException()
    {
        $this->calc->calculateDirectoryId($this->file);
    }
    
      
    
    
    
    
}
