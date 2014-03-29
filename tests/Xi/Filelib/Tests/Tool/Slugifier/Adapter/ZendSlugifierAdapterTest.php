<?php

namespace Xi\Filelib\Tests\Tool\Slugifier\Adapter;

use Xi\Filelib\Tool\Slugifier\Adapter\ZendSlugifierAdapter;

class ZendSlugifierTest extends TestCase
{
    /**
     * @xtest
     */
    public function slugifyShouldTransliterateViaTransliterator()
    {
        $trans = $this->getMock('Xi\Transliterator\Transliterator');

        $slugifier = new ZendSlugifierAdapter($trans);

        $trans->expects($this->once())->method('transliterate')->with('tussihovi');
        $slugifier->slugify('tussihovi');
    }

    public function setUp()
    {
        if (!class_exists('Zend\Filter\FilterChain')) {
            $this->markTestSkipped('Zend Framework 2 filters not loadable');
        }
        $this->slugifier = new ZendSlugifierAdapter();
    }
}
