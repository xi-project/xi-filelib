<?php

namespace Xi\Filelib\Tests\Folder\Command;

class FolderCommandTest extends \Xi\Filelib\Tests\TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Folder\Command\FolderCommand'));
    }
    
    
}

