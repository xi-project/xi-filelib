<?php

namespace Xi\Tests\Filelib\Publisher\Filesystem;

use Xi\Tests\Filelib\TestCase;

class AbstractPublisherTest extends TestCase
{

    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkCorrectly()
    {
        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\AbstractPublisher')
                ->setMethods(array(
                    'publish',
                    'unpublish',
                    'publishVersion',
                    'unpublishVersion',
                    'getUrl',
                    'getUrlVersion',
                ))
                ->getMock();
        
        $filelib = $this->getMockBuilder('Xi\Filelib\FileLibrary')->getMock();
                
        $this->assertNull($publisher->getFilelib());
        
        $publisher->setFilelib($filelib);
        
        $this->assertEquals($filelib, $publisher->getFilelib());
        
        
        
        
    }
    
    
    
    
}

?>
