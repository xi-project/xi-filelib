<?php

/*
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Linker\ReversibleSequentialLinker;
use Xi\Filelib\Folder\Folder;

/**
 * @group linker
 */
class ReversibleSequentialLinkerTest extends \Xi\Filelib\Tests\TestCase
{
    public function setUp()
    {
        $vp = $this->getMockedVersionProvider();

        $vp->expects($this->any())
             ->method('getExtension')
             ->with($this->isInstanceOf('Xi\Filelib\File\File'), 'xoo')
             ->will($this->returnValue('xoo'));

        $this->versionProvider = $vp;
    }

    public function provideFiles()
    {
        return array(
            array(
                File::create(array(
                    'id' => 888,
                    'name' => 'loso.png',
                    'folder_id' => 3,
                    'uuid' => 'uuid-888',
                )), 3, 48, '1/1/19/uuid-888-xoo.xoo',

            ),
            array(
                File::create(array(
                    'id' => 500346,
                    'name' => 'kim-jong-il',
                    'folder_id' => 4,
                    'uuid' => 'uuid-500346'
                )), 6, 14, '1/1/14/1/5/11/uuid-500346-xoo.xoo',

            ),
            array(
                File::create(array(
                    'id' => 1523291,
                    'name' => 'juurekas.nom',
                    'folder_id' => 1,
                    'uuid' => 'uuid-1523291'
                )), 8, 88, '1/1/1/1/1/3/21/63/uuid-1523291-xoo.xoo',

            ),
        );
    }

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function createsLinks($file, $levels, $fpd, $beautifurl)
    {
        $linker = new ReversibleSequentialLinker($levels, $fpd);
        $linker->attachTo($this->getMockedFilelib());

        $this->assertEquals(
            $beautifurl,
            $linker->getLink(
                $file,
                'xoo',
                $this->versionProvider->getExtension($file, 'xoo')
            )
        );
    }

    /**
     * @test
     */
    public function reversesLinks()
    {
        $fire = $this->getMockedFileRepository();
        $filelib = $this->getMockedFilelib(
            null,
            array(
                'fire' => $fire
            )
        );

        $linker = new ReversibleSequentialLinker(3, 100);
        $linker->attachTo($filelib);

        $file = File::create(array('uuid' => 'uuid-lusso-grande'));
        $fire
            ->expects($this->once())
            ->method('findByUuid')
            ->with('uuid-lusso-grande')
            ->will($this->returnValue($file));

        $link = '1/9/58/1457/uuid-lusso-grande-xoo.jpg';

        list ($reversed, $version) = $linker->reverseLink($link);

        $this->assertSame($file, $reversed);
        $this->assertEquals('xoo', $version);
    }


    /**
     * @test
     * @expectedException Xi\Filelib\InvalidArgumentException
     */
    public function getDirectoryIdShouldThrowExceptionWithNonNumericFileIds()
    {
        $linker = new ReversibleSequentialLinker(3, 100);
        $file = File::create(array('id' => 'xoo-xoo'));

        $linker->getDirectoryId($file);

    }

    /**
     * @test
     * @expectedException Xi\Filelib\InvalidArgumentException
     */
    public function getDirectoryIdShouldThrowExceptionWhenDirectoryLevelsIsLessThanOne()
    {
        $linker = new ReversibleSequentialLinker(0, 100);
        $file = File::create(array('id' => 1));

        $ret = $linker->getDirectoryId($file);
    }
}
