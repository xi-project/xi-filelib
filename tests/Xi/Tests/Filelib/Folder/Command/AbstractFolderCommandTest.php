<?php

namespace Xi\Tests\Filelib\Folder\Command;

class AbstractFolderCommandTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Command\AbstractFolderCommand'));
        $this->assertContains('Xi\Filelib\Folder\Command\FolderCommand', class_implements('Xi\Filelib\Folder\Command\AbstractFolderCommand'));
    }
    
    /**
     * @test
     */
    public function commandShouldInitializeProperly()
    {
        $folderOperator = $this->getMockForAbstractClass('Xi\Filelib\Folder\FolderOperator');
        
        $command = $this->getMockBuilder('Xi\Filelib\Folder\Command\AbstractFolderCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array($folderOperator))
                        ->getMockForAbstractClass();
        
        $this->assertSame($folderOperator, $command->getFolderOperator());
        
    }
    
    
    
}

