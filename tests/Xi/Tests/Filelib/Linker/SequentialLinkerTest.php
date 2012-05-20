<?php

namespace Xi\Tests\Filelib\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Linker\SequentialLinker;

class SequentialLinkerTest extends \Xi\Tests\Filelib\TestCase
{


    public function setUp()
    {

        $fo = $this->getMockBuilder('\Xi\Filelib\Folder\FolderOperator')->getMock();
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


                 }

                 return null;

             }));



        $this->fo = $fo;


        $this->filelib = $this->getFilelib();

        $this->filelib->setFolderOperator($fo);

        $vp = $this->getMock('\Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $vp->expects($this->any())
             ->method('getIdentifier')
             ->will($this->returnValue('xoo'));

        $vp->expects($this->any())
             ->method('getExtensionFor')
             ->with('xoo')
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
    public function linkerShouldCreateProperSequentialLinks($file, $levels, $fpd, $beautifurl)
    {
        $linker = new SequentialLinker();
        $linker->setDirectoryLevels($levels);
        $linker->setFilesPerDirectory($fpd);

        $linker->setFilelib($this->filelib);

        $this->assertEquals($beautifurl[0], $linker->getLink($file, true));
    }

    /**
     *
     *
     * @test
     * @dataProvider provideFiles
     */
    public function versionLinkerShouldCreateProperBeautifurlLinks($file, $levels, $fpd, $beautifurl)
    {
        $linker = new SequentialLinker();
        $linker->setDirectoryLevels($levels);
        $linker->setFilesPerDirectory($fpd);

        $linker->setFilelib($this->filelib);

        $this->assertEquals($beautifurl[1], $linker->getLinkVersion($file, $this->versionProvider->getIdentifier(), $this->versionProvider->getExtensionFor($this->versionProvider->getIdentifier())));
    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\InvalidArgumentException
     */
    public function getDirectoryIdShouldThrowExceptionWithNonNumericFileIds()
    {
        $linker = new SequentialLinker();
        $file = File::create(array('id' => 'xoo-xoo'));

        $linker->getDirectoryId($file);

    }


}
