<?php

namespace Xi\Filelib\Tests\Tool\Slugifier;

class AbstractZendSlugifierTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\Slugifier\AbstractZendSlugifier'));
        $this->assertContains('Xi\Filelib\Tool\Slugifier\Slugifier', class_implements('Xi\Filelib\Tool\Slugifier\AbstractZendSlugifier'));
    }

    /**
     * @test
     */
    public function getTransliteratorShouldReturnTransliterator()
    {
        $trans = $this->getMock('Xi\Filelib\Tool\Transliterator\Transliterator');

        $slugifier = $this->getMockBuilder('Xi\Filelib\Tool\Slugifier\AbstractZendSlugifier')
                          ->setConstructorArgs(array($trans))
                          ->getMockForAbstractClass();

        $this->assertSame($trans, $slugifier->getTransliterator());
    }

    /**
     * @test
     */
    public function slugifyShouldTransliterateViaTransliterator()
    {
        $trans = $this->getMock('Xi\Filelib\Tool\Transliterator\Transliterator');

        $slugifier = $this->getMockBuilder('Xi\Filelib\Tool\Slugifier\AbstractZendSlugifier')
                          ->setConstructorArgs(array($trans))
                          ->getMockForAbstractClass();

        $slugifier->expects($this->any())->method('getFilter')->will($this->returnValue($this->getMock('Zend\Filter\FilterChain')));

        $trans->expects($this->once())->method('transliterate')->with('tussihovi');
        $slugifier->slugify('tussihovi');

    }

}
