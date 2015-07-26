<?php

namespace Xi\Filelib\Tests\Storage;

use Xi\Filelib\Storage\Retrieved;

class RetrievedTest extends \Xi\Filelib\Tests\TestCase
{
    public function setUp()
    {
        copy(ROOT_TESTS . '/data/self-lussing-manatee.jpg', ROOT_TESTS . '/data/temp/sad-manatee.jpg');
    }

    public function tearDown()
    {
        if (is_file(ROOT_TESTS . '/data/temp/sad-manatee.jpg')) {
            unlink(ROOT_TESTS . '/data/temp/sad-manatee.jpg');
        }
    }

    /**
     * @test
     */
    public function initializes()
    {
        $this->assertFileExists(ROOT_TESTS . '/data/temp/sad-manatee.jpg');
        $retrieved = new Retrieved(ROOT_TESTS . '/data/temp/sad-manatee.jpg', false);
        $this->assertEquals(ROOT_TESTS . '/data/temp/sad-manatee.jpg', $retrieved->getPath());
    }
}
