<?php

namespace Xi\Tests\Filelib\Event;

class IdentifiableEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function interfaceExists()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Event\IdentifiableEvent'));
    }
}
