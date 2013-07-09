<?php

namespace Xi\Filelib\Tests\Tool\Slugifier;

use Xi\Filelib\Tool\Slugifier\ZendSlugifier;

class ZendSlugifierTest extends TestCase
{
    /**
     * @test
     */
    public function slugifyShouldTransliterateViaTransliterator()
    {
        $trans = $this->getMock('Xi\Transliterator\Transliterator');

        $slugifier = new ZendSlugifier($trans);

        $trans->expects($this->once())->method('transliterate')->with('tussihovi');
        $slugifier->slugify('tussihovi');
    }

    public function setUp()
    {
        if (!class_exists('Zend\Filter\FilterChain')) {
            $this->markTestSkipped('Zend Framework 2 filters not loadable');
        }

        $trans = $this->getMock('Xi\Transliterator\Transliterator');
        $trans->expects($this->any())->method('transliterate')->will($this->returnArgument(0));
        $this->slugifier = new ZendSlugifier($trans);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\Slugifier\ZendSlugifier'));
        $this->assertContains(
            'Xi\Filelib\Tool\Slugifier\Slugifier',
            class_implements('Xi\Filelib\Tool\Slugifier\ZendSlugifier')
        );
    }
}
