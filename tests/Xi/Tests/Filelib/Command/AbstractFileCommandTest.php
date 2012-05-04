<?php

namespace Xi\Tests\Filelib\File\Command;

class AbstractFileCommandTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\AbstractFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\AbstractFileCommand'));
    }
    
    /**
     * @test
     */
    public function commandShouldInitializeProperly()
    {
        $fileOperator = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        
        $command = $this->getMockBuilder('Xi\Filelib\File\Command\AbstractFileCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array($fileOperator))
                        ->getMockForAbstractClass();
        
        $this->assertSame($fileOperator, $command->getFileOperator());
        
    }
    
    
    
}

