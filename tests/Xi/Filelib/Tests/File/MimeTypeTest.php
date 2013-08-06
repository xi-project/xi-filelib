<?php

namespace Xi\Filelib\Tests\File;
use Xi\Filelib\Tests\TestCase;

use Xi\Filelib\File\MimeType;

class MimeTypeTest extends TestCase
{
    /**
     * @test
     */
    public function invalidMimeTypeShouldNotResolveToExtension()
    {
        $this->assertCount(0, MimeType::mimeTypeToExtensions('gran/lusso'));
    }

    public function provideMimeTypesAndExtensions()
    {
        return array(
            array('image/jpeg', 'jpg'),
            array('image/png', 'png'),
            array('video/x-msvideo', 'avi'),
            array('application/vnd.oasis.opendocument.text', 'odt')
        );
    }

    /**
     * @dataProvider provideMimeTypesAndExtensions
     * @test
     */
    public function mimeTypesShouldResolveToExtensions($mimeType, $expectedExtension)
    {
        $extensions = MimeType::mimeTypeToExtensions($mimeType);
        $this->assertGreaterThanOrEqual(1, count($extensions));
        $this->assertContains($expectedExtension, $extensions);
    }

    /**
     * @dataProvider provideMimeTypesAndExtensions
     * @test
     */
    public function extensionShouldResolveToMimeType($expectedMimeType, $extension)
    {
        $mimetype = MimeType::extensionToMimeType($extension);
        $this->assertSame($expectedMimeType, $mimetype);
    }

}
