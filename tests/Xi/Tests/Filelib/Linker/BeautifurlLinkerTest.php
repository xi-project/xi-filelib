<?php

/*
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Linker;

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Linker\BeautifurlLinker;
use Xi\Filelib\Tool\Slugifier\Zend2Slugifier;
use Xi\Filelib\Tool\Transliterator\StupidTransliterator;

/**
 * @group linker
 */
class BeautifurlLinkerTest extends \Xi\Tests\Filelib\TestCase
{
    private $linker;

    public function setUp()
    {
        if (!class_exists('Zend\Filter\FilterChain')) {
            $this->markTestSkipped('Zend Framework 2 filters not loadable');
        }

        $fo = $this->getMock('Xi\Filelib\Folder\FolderOperator');
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



                 } elseif($id == 2) {

                     return Folder::create(array(
                         'id' => 2,
                         'name' => 'lussuttaja',
                         'parent_id' => 1,
                         'url' => '/lussuttaja'
                     ));

                 } elseif($id == 3) {

                     return Folder::create(array(
                         'id' => 2,
                         'name' => 'tussin',
                         'parent_id' => 2,
                         'url' => '/lussuttaja/tussin'
                     ));


                 } elseif($id == 4) {

                     return Folder::create(array(
                         'id' => 2,
                         'name' => 'banaanin',
                         'parent_id' => 2,
                         'url' => '/lussuttaja/banaanin'
                     ));

                 } elseif($id == 5) {

                     return Folder::create(array(
                         'id' => 5,
                         'name' => 'sûürën ÜGRÎLÄISÊN KÄNSÄN SïëLú',
                         'parent_id' => 4,
                         'url' => '/lussuttaja/banaanin/suuren-ugrilaisen-kansan-sielu'
                     ));
                 }

                 return null;

             }));

        $trans = new StupidTransliterator();
        $slugifier = new Zend2Slugifier($trans);
        $this->linker = new BeautifurlLinker($fo, $slugifier);
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
    public function linkerShouldCreateProperBeautifurlLinks($file, $beautifurl)
    {
        $this->linker->setExcludeRoot(true);
        $this->linker->setSlugify(true);

        $this->assertEquals($beautifurl[0], $this->linker->getLink($file, true));
    }

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function versionLinkerShouldCreateProperBeautifurlLinks($file, $beautifurl)
    {
        $this->linker->setExcludeRoot(true);
        $this->linker->setSlugify(true);

        $versionProvider = $this->getMock('\Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $versionProvider->expects($this->any())
                        ->method('getIdentifier')
                        ->will($this->returnValue('xoo'));

        $versionProvider->expects($this->any())
                        ->method('getExtensionFor')
                        ->with($this->equalTo('xoo'))
                        ->will($this->returnValue('xoo'));

        $this->assertEquals(
            $beautifurl[1],
            $this->linker->getLinkVersion(
                $file,
                $versionProvider->getIdentifier(),
                $versionProvider->getExtensionFor($versionProvider->getIdentifier())
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

        $this->linker->setExcludeRoot(false);
        $this->assertEquals('root/lussuttaja/lamantiini.lus', $this->linker->getLink($file));

        $this->linker->setExcludeRoot(true);
        $this->assertEquals('lussuttaja/lamantiini.lus', $this->linker->getLink($file));
    }

    /**
     * @test
     */
    public function linkerShouldNotSlugifyWhenSlugifyIsSetToFalse()
    {
        $this->linker->setSlugify(false);

        $file = File::create(array(
            'name' => 'lamantiini.lus',
            'folder_id' => 5,
            'resource' => Resource::create(array('id' => 1)),
        ));

        $this->assertEquals(
            'root/lussuttaja/banaanin/sûürën ÜGRÎLÄISÊN KÄNSÄN SïëLú/lamantiini.lus',
             $this->linker->getLink($file)
        );
    }


    /**
     * @test
     */
    public function excludeRootSetterAndGetterShouldWorkAsExpected()
    {
         $this->assertFalse($this->linker->getExcludeRoot());
         $this->assertSame($this->linker, $this->linker->setExcludeRoot(true));
         $this->assertTrue($this->linker->getExcludeRoot());
    }

    /**
     * @test
     */
    public function slugifyRootSetterAndGetterShouldWorkAsExpected()
    {
         $this->assertTrue($this->linker->getSlugify());
         $this->assertSame($this->linker, $this->linker->setSlugify(false));
         $this->assertFalse($this->linker->getSlugify());
    }

    /**
     * @test
     */
    public function getSlugifierShouldReturnSlugifier()
    {
        $mockSlugifier = $this->getMock('Xi\Filelib\Tool\Slugifier\Slugifier');
        $mockOperator = $this->getMock('Xi\Filelib\Folder\FolderOperator');

        $linker = new BeautifurlLinker($mockOperator, $mockSlugifier);

        $slugifier = $linker->getSlugifier();

        $this->assertSame($mockSlugifier, $slugifier);
    }

    /**
     * @test
     */
    public function takesOptionalOptionsInConstructor()
    {
        $linker = new BeautifurlLinker(
            $this->getMock('Xi\Filelib\Folder\FolderOperator'),
            $this->getMock('Xi\Filelib\Tool\Slugifier\Slugifier'),
            array(
                'slugify'        => false,
                'excludeRoot'    => true,
            )
        );

        $this->assertFalse($linker->getSlugify());
        $this->assertTrue($linker->getExcludeRoot());
    }
}
