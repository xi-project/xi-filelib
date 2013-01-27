<?php

namespace Xi\Filelib\Tests\File\Command;

class FileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\File\Command\FileCommand'));
    }

}
