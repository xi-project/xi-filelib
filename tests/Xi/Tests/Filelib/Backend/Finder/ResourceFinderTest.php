<?php

namespace Xi\Tests\Filelib\Backend\Finder;

use Xi\Filelib\Backend\Finder\ResourceFinder;

class ResourceFinderTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Backend\Finder\ResourceFinder'));
        $this->assertContains('Xi\Filelib\Backend\Finder\Finder', class_implements('Xi\Filelib\Backend\Finder\ResourceFinder'));
    }


    public function getExpectedFields()
    {
        return array(
            'id',
            'hash'
        );
    }

    public function getExpectedResultClass()
    {
        return 'Xi\Filelib\File\Resource';
    }


    public function setUp()
    {
        $this->finder = new ResourceFinder();
    }


}
