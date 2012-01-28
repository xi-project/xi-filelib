<?php

namespace Xi\Tests\Filelib\Backend;

/**
 * @author Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 */
abstract class RelationalDbTestCase extends DbTestCase
{
    /**
     * @test
     */
    public function findRootFolderShouldReturnRootFolder()
    {
        $folder = $this->backend->findRootFolder();

        $this->assertArrayHasKey('id', $folder);
        $this->assertArrayHasKey('parent_id', $folder);
        $this->assertArrayHasKey('name', $folder);
        $this->assertArrayHasKey('url', $folder);

        $this->assertNull($folder['parent_id']);
    }
}
