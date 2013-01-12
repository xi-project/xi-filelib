<?php

namespace Xi\Tests\Filelib\Backend\Finder;

use Xi\Filelib\Backend\Finder\FolderFinder;

class FolderFinderTest extends TestCase
{
    public function setUp()
    {
        $this->finder = new FolderFinder();
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Backend\Finder\FolderFinder'));
        $this->assertContains(
            'Xi\Filelib\Backend\Finder\Finder',
            class_implements('Xi\Filelib\Backend\Finder\FolderFinder')
        );
    }

    public function getExpectedFields()
    {
        return array(
            'id',
            'parent_id',
            'url',
        );
    }

    public function getExpectedResultClass()
    {
        return 'Xi\Filelib\Folder\Folder';
    }
}
