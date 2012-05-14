<?php

namespace Xi\Tests\Filelib\Folder\Command;

class FolderCommandTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Folder\Command\FolderCommand'));
    }
    
    
}

