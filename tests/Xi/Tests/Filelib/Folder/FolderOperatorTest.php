<?php

namespace Xi\Tests\Folderlib\Folder;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class FolderOperatorTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Folder\FolderOperator'));
    }
    
    
}