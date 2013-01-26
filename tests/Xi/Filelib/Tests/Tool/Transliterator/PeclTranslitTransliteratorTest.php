<?php

namespace Xi\Filelib\Tests\Tool\Transliterator;

use Xi\Filelib\Tool\Transliterator\PeclTranslitTransliterator;

class PeclTranslitTransliteratorTest extends TestCase
{

    public function setUp()
    {
        if (!extension_loaded("translit")) {
            $this->markTestSkipped("PECL translit extension not loaded");
        }

    }

    public function getTransliteratorWithDefaultSettings()
    {
        return new PeclTranslitTransliterator();
    }

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\Transliterator\PeclTranslitTransliterator'));
        $this->assertContains('Xi\Filelib\Tool\Transliterator\Transliterator', class_implements('Xi\Filelib\Tool\Transliterator\PeclTranslitTransliterator'));
    }
}
