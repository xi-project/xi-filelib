<?php

namespace Xi\Tests\Filelib\Tool\Transliterator;

use Xi\Filelib\Tool\Transliterator\StupidTransliterator;

class StupidTransliteratorTest extends TestCase
{

    public function getTransliteratorWithDefaultSettings()
    {
        return new StupidTransliterator();
    }


    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\Transliterator\StupidTransliterator'));
        $this->assertContains('Xi\Filelib\Tool\Transliterator\Transliterator', class_implements('Xi\Filelib\Tool\Transliterator\StupidTransliterator'));
    }
}
