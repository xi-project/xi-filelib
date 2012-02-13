<?php

namespace Xi\Tests\Folderlib\Folder;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class DefaultFolderOperatorTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\DefaultFolderOperator'));
    }
    
    
}