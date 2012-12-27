<?php

namespace Xi\Tests\Filelib;

use Xi\Filelib\Version;

class VersionTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Version'));
    }

    public function provideVersions()
    {
        return array(
            array(-1, '0.6.0'),
            array(-1, '0.7.0dev'),
            array(-1, '0.7.0'),
            array(0, '0.8.0dev'),
            array(1, '0.8.0'),
            array(1, '0.9.0'),
            array(1, '0.10.0'),
            array(1, '1.0.0'),
        );
    }

    /**
     * @test
     * @dataProvider provideVersions
     */
    public function testVersionShouldBeCorrect($expected, $version)
    {
        $this->assertEquals($expected, Version::compare($version));
    }

}
