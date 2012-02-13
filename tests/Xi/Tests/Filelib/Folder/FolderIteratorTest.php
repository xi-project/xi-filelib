<?php

namespace Xi\Tests\Folderlib\Folder;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class FolderIteratorTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\FolderIterator'));
    }
    
    
}