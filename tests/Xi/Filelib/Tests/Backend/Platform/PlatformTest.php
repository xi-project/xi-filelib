<?php

namespace Xi\Filelib\Tests\Backend\Platform;

use Xi\Filelib\Tests\TestCase as FilelibTestCase;

/**
 * @group backend
 */
class PlatformTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Backend\Platform\Platform'));
    }
}
