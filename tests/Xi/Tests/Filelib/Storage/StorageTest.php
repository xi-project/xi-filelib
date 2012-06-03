<?php

namespace Xi\Tests\Filelib\Storage;

use Xi\Tests\Filelib\TestCase;

/**
 * @group storage
 */
class StorageTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Storage\Storage'));
    }
}
