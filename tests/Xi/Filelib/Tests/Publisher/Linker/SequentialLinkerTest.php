<?php

/*
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Publisher\Linker\SequentialLinker;
use Xi\Filelib\Folder\Folder;

/**
 * @group linker
 */
class SequentialLinkerTest extends \Xi\Filelib\Tests\TestCase
{
    public function setUp()
    {
        $this->version = Version::get('xoo');

        $fo = $this->getMockBuilder('\Xi\Filelib\Folder\FolderRepository')->disableOriginalConstructor()->getMock();
        $fo->expects($this->any())
             ->method('find')
             ->will($this->returnCallback(function($id) {

                 if ($id == 1) {
                     return Folder::create(array(
                         'id' => 1,
                         'name' => 'root',
                         'parent_id' => null,
                         'url' => ''
                     ));

                 } elseif ($id == 2) {
                     return Folder::create(array(
                         'id' => 2,
                         'name' => 'lussuttaja',
                         'parent_id' => 1,
                         'url' => '/lussuttaja'
                     ));

                 } elseif ($id == 3) {
                     return Folder::create(array(
                         'id' => 2,
                         'name' => 'tussin',
                         'parent_id' => 2,
                         'url' => '/lussuttaja/tussin'
                     ));

                 } elseif ($id == 4) {
                     return Folder::create(array(
                         'id' => 2,
                         'name' => 'banaanin',
                         'parent_id' => 2,
                         'url' => '/lussuttaja/banaanin'
                     ));

                 }

                 return null;

             }));

        $this->fo = $fo;

        $vp = $this->getMockedVersionProvider();

        $vp->expects($this->any())
             ->method('getExtension')
             ->with($this->isInstanceOf('Xi\Filelib\File\File'), $this->version)
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
                )), 3, 48, array('1/1/19/loso.png', '1/1/19/loso-xoo.xoo'),

            ),
            array(
                File::create(array(
                    'id' => 500346,
                    'name' => 'kim-jong-il',
                    'folder_id' => 4

                )), 6, 14, array('1/1/14/1/5/11/kim-jong-il', '1/1/14/1/5/11/kim-jong-il-xoo.xoo'),

            ),
            array(
                File::create(array(
                    'id' => 1523291,
                    'name' => 'juurekas.nom',
                    'folder_id' => 1

                )), 8, 88, array('1/1/1/1/1/3/21/63/juurekas.nom', '1/1/1/1/1/3/21/63/juurekas-xoo.xoo'),

            ),
        );
    }

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function versionLinkerShouldCreateProperBeautifurlLinks($file, $levels, $fpd, $beautifurl)
    {
        $linker = new SequentialLinker($levels, $fpd);
        $linker->attachTo($this->getMockedFilelib());

        $this->assertEquals(
            $beautifurl[1],
            $linker->getLink(
                $file,
                $this->version,
                $this->versionProvider->getExtension($file, $this->version)
            )
        );

    }
}
