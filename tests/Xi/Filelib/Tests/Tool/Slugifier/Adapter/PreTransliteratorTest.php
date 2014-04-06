<?php

namespace Xi\Filelib\Tests\Tool\Slugifier\Adapter;

use Xi\Filelib\Tool\Slugifier\Adapter\PreTransliterator;

class PreTransliteratorTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function transliteratesAndDelegates()
    {
        $transliterator = $this->getMock('Xi\Transliterator\Transliterator');
        $adapter = $this->getMock('Xi\Filelib\Tool\Slugifier\Adapter\SlugifierAdapter');

        $pretrans = new PreTransliterator(
            $transliterator,
            $adapter
        );

        $transliterator
            ->expects($this->once())
            ->method('transliterate')
            ->with('tüssi')->will($this->returnValue('tussi'));

        $adapter
            ->expects($this->once())
            ->method('slugify')
            ->with('tussi')
            ->will($this->returnValue('tussi'));

        $ret = $pretrans->slugify('tüssi');
        $this->assertEquals('tussi', $ret);
    }
}
