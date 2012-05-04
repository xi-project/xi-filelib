<?php

namespace Xi\Tests\Filelib\File\Command;


class FileCommandTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\File\Command\FileCommand'));
    }
    
    
}

