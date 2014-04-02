<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\File\Upload\FileUpload;

class FileUploadTest extends TestCase
{
    /**
     * @test
     *
     */
    public function throwsUpIfPathIsInvalid()
    {
        $this->setExpectedException('Xi\Filelib\RuntimeException');
        new FileUpload(ROOT_TESTS . '/invalid-file.lus');
    }

    /**
     * @test
     */
    public function basicGettersAndSettersShouldWork()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $this->assertNull($upload->getOverrideBasename());
        $this->assertNull($upload->getOverrideFilename());
        $this->assertInstanceOf('DateTime', $upload->getDateUploaded());
        $this->assertEquals('image/jpeg', $upload->getMimeType());
        $this->assertEquals(23239, $upload->getSize());
        $this->assertEquals(ROOT_TESTS . '/data/self-lussing-manatee.jpg', $upload->getRealPath());

        $overrideBaseName = 'lussen';
        $overrideFileName = 'lussen.hof';
        $overrideDate = new DateTime('1978-03-21 09:09:09');

        $upload->setOverrideBaseName($overrideBaseName);
        $upload->setOverrideFileName($overrideFileName);
        $upload->setDateUploaded($overrideDate);

        $this->assertEquals($overrideBaseName, $upload->getOverrideBasename());
        $this->assertEquals($overrideFileName, $upload->getOverrideFilename());
        $this->assertEquals($overrideDate, $upload->getDateUploaded());
    }

    /**
     * @test
     */
    public function nonTemporaryUploadShouldNotBeDeletedOnDestruct()
    {
        $path = ROOT_TESTS . '/data/temp/self-lussing-manatee-clone.jpg';

        copy(ROOT_TESTS . '/data/self-lussing-manatee.jpg', $path);

        $this->assertFileExists($path);

        $upload = new FileUpload($path);

        unset($upload);

        $this->assertFileExists($path);

        unlink($path);
    }

    /**
     * @test
     */
    public function temporaryUploadShouldBeDeletedOnDestruct()
    {
        $path = ROOT_TESTS . '/data/temp/self-lussing-manatee-clone.jpg';

        copy(ROOT_TESTS . '/data/self-lussing-manatee.jpg', $path);

        $this->assertFileExists($path);

        $upload = new FileUpload($path);
        $upload->setTemporary(true);

        $this->assertTrue($upload->isTemporary());

        unset($upload);

        $this->assertFileNotExists($path);
    }

    public function provideOverrideBaseNames()
    {
        return array(
            array('llluuuudendorf.jpg', 'llluuuudendorf'),
            array('tussi.lussi.jpg', 'tussi.lussi'),
            array('tohtori Vesala.jpg', 'tohtori Vesala'),
            array('123xooxer.jpg', '123xooxer'),
        );
    }

    /**
     * @test
     * @dataProvider provideOverrideBaseNames
     */
    public function overrideBaseNameShouldOverrideBaseName($expectedFilename, $baseName)
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $upload = new FileUpload($path);
        $upload->setOverrideBasename($baseName);

        $this->assertEquals($expectedFilename, $upload->getUploadFilename());
    }

    /**
     * @test
     */
    public function overrideFileNameShouldOverrideFileName()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $upload = new FileUpload($path);
        $upload->setOverrideFilename('tussin-lussu.luz');

        $this->assertEquals('tussin-lussu.luz', $upload->getUploadFilename());

        $upload->setOverrideBasename('luudendorf');

        $this->assertEquals('luudendorf.luz', $upload->getUploadFilename());
    }
}
