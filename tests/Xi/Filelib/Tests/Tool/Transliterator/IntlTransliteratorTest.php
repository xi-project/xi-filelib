<?php

namespace Xi\Filelib\Tests\Tool\Transliterator;

use Xi\Filelib\Tool\Transliterator\IntlTransliterator;

class IntlTransliteratorTest extends TestCase
{

    public function setUp()
    {
        if (!class_exists("Transliterator")) {
            $this->markTestSkipped("Transliterator class (from intl extension 2.0+) not found");
        }
    }

    public function getTransliteratorWithDefaultSettings()
    {
        return new IntlTransliterator();
    }

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\Transliterator\IntlTransliterator'));
        $this->assertContains('Xi\Filelib\Tool\Transliterator\Transliterator', class_implements('Xi\Filelib\Tool\Transliterator\IntlTransliterator'));
    }
}
