<?php

namespace Xi\Tests\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Event\FileEvent;

class FileEventTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\FileEvent'));
        $this->assertContains('Symfony\Component\EventDispatcher\Event', class_parents('Xi\Filelib\Event\FileEvent'));
    }
    
    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $file = $this->getMock('Xi\Filelib\File\File');
        
        $event = new FileEvent($file);
        
        $file2 = $event->getFile();
        
        $this->assertSame($file, $file2);
        
    }
    
    
}
