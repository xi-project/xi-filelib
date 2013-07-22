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
use Xi\Filelib\File\Resource;
use Xi\Filelib\Publisher\Linker\BeautifurlLinker;

use Xi\Transliterator\StupidTransliterator;
use Xi\Filelib\Tool\Slugifier\ZendSlugifier;

/**
 * @group linker
 */
class BeautifurlLinkerTest extends \Xi\Filelib\Tests\TestCase
{
    private $filelib;

    private $slugifier;

    public function setUp()
    {
        if (!class_exists('Zend\Filter\FilterChain')) {
            $this->markTestSkipped('Zend Framework 2 filters not loadable');
        }

        if (!extension_loaded('intl')) {
            $this->markTestSkipped('Intl extension must be loaded');
        }

        $foop = $this->getMockedFolderOperator();
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
                         'name' => 'sûürën ÜGRÎLÄISÊN KÄNSÄN SïëLú',
                         'parent_id' => 4,
                         'url' => '/lussuttaja/banaanin/suuren-ugrilaisen-kansan-sielu'
                     ));
                 }

                 return null;

             }));

        $this->filelib = $this->getMockedFilelib(null, null, $foop);
        $this->slugifier = new ZendSlugifier(new StupidTransliterator());

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
                )), array('lussuttaja/banaanin/suuren-ugrilaisen-kansan-sielu/salainen-suunnitelma.pdf', 'lussuttaja/banaanin/suuren-ugrilaisen-kansan-sielu/salainen-suunnitelma-xoo.xoo'),

            ),

        );
    }

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function versionLinkerShouldCreateProperBeautifurlLinks($file, $beautifurl)
    {
        $linker = new BeautifurlLinker($this->filelib, $this->slugifier, true);

        $versionProvider = $this->getMock('\Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $versionProvider->expects($this->any())
                        ->method('getIdentifier')
                        ->will($this->returnValue('xoo'));

        $versionProvider->expects($this->any())
                        ->method('getExtensionFor')
                        ->with($this->isInstanceOf('Xi\Filelib\File\File'), $this->equalTo('xoo'))
                        ->will($this->returnValue('xoo'));

        $this->assertEquals(
            $beautifurl[1],
            $linker->getLink(
                $file,
                $versionProvider->getIdentifier(),
                $versionProvider->getExtensionFor($file, $versionProvider->getIdentifier())
            )
        );
    }

    /**
     * @test
     */
    public function linkerShouldExcludeRootProperly()
    {
        $file = File::create(array(
            'name' => 'lamantiini.lus',
            'folder_id' => 2,
            'resource' => Resource::create(array('id' => 1)),
        ));

        $linker = new BeautifurlLinker($this->filelib, $this->slugifier, false);
        $this->assertEquals('root/lussuttaja/lamantiini-loso.lus', $linker->getLink($file, 'loso', 'lus'));

        $linker = new BeautifurlLinker($this->filelib, $this->slugifier, true);
        $this->assertEquals('lussuttaja/lamantiini-loso.lus', $linker->getLink($file, 'loso', 'lus'));
    }

    /**
     * @test
     */
    public function linkerShouldNotSlugifyWhenTheresNoSlugifier()
    {
        $linker = new BeautifurlLinker($this->filelib, null, false);

        $file = File::create(array(
            'name' => 'lamantiini.lus',
            'folder_id' => 5,
            'resource' => Resource::create(array('id' => 1)),
        ));

        $this->assertEquals(
            'root/lussuttaja/banaanin/sûürën ÜGRÎLÄISÊN KÄNSÄN SïëLú/lamantiini-loso.lus',
             $linker->getLink($file, 'loso', 'lus')
        );
    }

    /**
     * @test
     */
    public function excludeRootGetterShouldWork()
    {
        $linker = new BeautifurlLinker($this->filelib, null, false);
        $this->assertFalse($linker->getExcludeRoot());
    }

    /**
     * @test
     */
    public function getSlugifierShouldReturnSlugifier()
    {
        $linker = new BeautifurlLinker($this->filelib, $this->slugifier, false);
        $slugifier = $linker->getSlugifier();
        $this->assertSame($this->slugifier, $slugifier);
    }
}
