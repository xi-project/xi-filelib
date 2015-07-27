<?php

/*
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Publisher\Linker;

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Version;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Publisher\Linker\BeautifurlLinker;

/**
 * @group linker
 */
class BeautifurlLinkerTest extends \Xi\Filelib\Tests\TestCase
{
    private $filelib;

    private $slugifier;

    private $version;

    public function setUp()
    {
        $this->version = Version::get('xoo');

        $foop = $this->getMockedFolderRepository();
        $foop->expects($this->any())
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

                 } elseif ($id == 5) {
                     return Folder::create(array(
                         'id' => 5,
                         'name' => 'sûürën ÜGRÎLÄISÊN KÄNSÄN Sïëlú',
                         'parent_id' => 4,
                         'url' => '/lussuttaja/banaanin/sûürën-ÜGRÎLÄISÊN-KÄNSÄN-Sïëlú'
                     ));
                 }

                 return null;

             }));

        $this->filelib = $this->getMockedFilelib(null, null, $foop);
    }

    public function provideFiles()
    {
        return array(
            array(
                File::create(array(
                    'name' => 'loso.png',
                    'folder_id' => 3,
                    'resource' => Resource::create(array('id' => 1)),
                )), array('lussuttaja/tussin/loso.png', 'lussuttaja/tussin/loso-xoo.xoo'),

            ),
            array(
                File::create(array(
                    'name' => 'kim-jong-il',
                    'folder_id' => 4,
                    'resource' => Resource::create(array('id' => 1)),
                )), array('lussuttaja/banaanin/kim-jong-il', 'lussuttaja/banaanin/kim-jong-il-xoo.xoo'),

            ),
            array(
                File::create(array(
                    'name' => 'juurekas.nom',
                    'folder_id' => 1,
                    'resource' => Resource::create(array('id' => 1)),
                )), array('juurekas.nom', 'juurekas-xoo.xoo'),

            ),
            array(
                File::create(array(
                    'name' => 'salainen-suunnitelma.pdf',
                    'folder_id' => 5,
                    'resource' => Resource::create(array('id' => 1)),
                )), array('lussuttaja/banaanin/suueren-uegrilaeisen-kaensaen-sielu/salainen-suunnitelma.pdf', 'lussuttaja/banaanin/suueren-uegrilaeisen-kaensaen-sielu/salainen-suunnitelma-xoo.xoo'),

            ),

        );
    }

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function versionLinkerShouldCreateProperBeautifurlLinks($file, $beautifurl)
    {
        $linker = new BeautifurlLinker();
        $linker->attachTo($this->filelib);

        $versionProvider = $this->getMockedVersionProvider();
        $versionProvider->expects($this->any())
                        ->method('getExtension')
                        ->with($this->isInstanceOf('Xi\Filelib\File\File'), $this->version)
                        ->will($this->returnValue('xoo'));

        $this->assertEquals(
            $beautifurl[1],
            $linker->getLink(
                $file,
                $this->version,
                $versionProvider->getExtension($file, $this->version)
            )
        );
    }

    /**
     * @test
     */
    public function linkerShouldExcludeRootProperly()
    {
        $extension = 'lus';

        $file = File::create(array(
            'name' => 'lamantiini.lus',
            'folder_id' => 2,
            'resource' => Resource::create(array('id' => 1)),
        ));

        $linker = new BeautifurlLinker();
        $linker->attachTo($this->filelib);

        $this->assertEquals(
            'lussuttaja/lamantiini-loso.lus',
            $linker->getLink($file, Version::get('loso'), $extension)
        );
    }
}
