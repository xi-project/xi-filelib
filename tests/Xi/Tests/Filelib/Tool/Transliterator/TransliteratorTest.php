<?php

namespace Xi\Tests\Filelib\Tool\Transliterator;

class TransliteratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Tool\Transliterator\Transliterator'));
    }
}
