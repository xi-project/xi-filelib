<?php

namespace Xi\Tests\Folderlib\Folder;

use Xi\Filelib\Folder\DefaultFolderOperator;

class DefaultFolderOperatorTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\DefaultFolderOperator'));
    }
    
        
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFolderOperator($filelib);
        
        $val = 'Lussen\Hofer';
        $this->assertEquals('Xi\Filelib\Folder\FolderItem', $op->getClass());
        $this->assertSame($op, $op->setClass($val));
        $this->assertEquals($val, $op->getClass());

    }
    

}