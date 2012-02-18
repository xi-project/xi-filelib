<?php

namespace Xi\Tests\Filelib\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\File\FileItem;
use Xi\Filelib\Folder\FolderItem;

class DefaultFileOperatorTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\DefaultFileOperator'));
    }
    
    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);
                
        $val = 'Lussen\Hofer';
        $this->assertEquals('Xi\Filelib\File\FileItem', $op->getClass());
        $this->assertSame($op, $op->setClass($val));
        $this->assertEquals($val, $op->getClass());
        
        /*
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $this->assertEquals(null, $profile->getFilelib());
        $this->assertSame($profile, $profile->setFilelib($filelib));
        $this->assertSame($filelib, $profile->getFilelib());
        */
    }
    
    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfConfiguredClassWithNoData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);
        
        $mockClass = $this->getMockClass('Xi\Filelib\File\FileItem');
        
        $file = $op->getInstance();
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);
    
        $op->setClass($mockClass);

        $file = $op->getInstance();
        $this->assertInstanceOf($mockClass, $file);
        
    }
    
    
    
    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfConfiguredClassWithData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);
    
        $data = array(
            'mimetype' => 'luss/xoo'
        );
        
        $file = $op->getInstance($data);
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);
    
        $this->assertSame($filelib, $file->getFilelib());
        $this->assertEquals('luss/xoo', $file->getMimetype());
        
    }
    
    /**
     * @test
     */
    public function addProfileShouldAddProfile()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFileOperator($filelib);
        
        $this->assertEquals(array(), $op->getProfiles());
        
        $linker = $this->getMockForAbstractClass('Xi\Filelib\Linker\Linker');
        $linker->expects($this->once())->method('setFilelib')->with($this->equalTo($filelib));

        $linker2 = $this->getMockForAbstractClass('Xi\Filelib\Linker\Linker');
        $linker2->expects($this->once())->method('setFilelib')->with($this->equalTo($filelib));
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getIdentifier')->will($this->returnValue('xooxer'));
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $profile2 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile2->expects($this->any())->method('getIdentifier')->will($this->returnValue('lusser'));
        $profile2->expects($this->any())->method('getLinker')->will($this->returnValue($linker2));
        
        $op->addProfile($profile);
        $this->assertCount(1, $op->getProfiles());
        
        $op->addProfile($profile2);
        $this->assertCount(2, $op->getProfiles());
        
        $this->assertSame($profile, $op->getProfile('xooxer'));
        $this->assertSame($profile2, $op->getProfile('lusser'));
        
    }
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getProfileShouldFailWhenProfileDoesNotExist()
    {
       $filelib = $this->getMock('Xi\Filelib\FileLibrary');
       $op = new DefaultFileOperator($filelib);
       
       $prof = $op->getProfile('xooxer');
    }
    
    /**
     * @test
     */
    public function updateShouldDelegateCorrectlyWhenFileCanNotBePublished()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'publish', 'isReadable', 'isReadableByAnonymous', 'getProfile'))
                   ->getMock();
        
        
        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->once())->method('getLink')->will($this->returnValue('maximuslincitus'));
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $file = $this->getMockBuilder('Xi\Filelib\File\FileItem')
                     ->setMethods(array('setLink'))
                     ->getMock();
        
        $file->setProfile('lussenhofer');
        
        $file->expects($this->once())->method('setLink')->with($this->equalTo('maximuslincitus'));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('updateFile')->with($this->equalTo($file));

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        
        $op->expects($this->once())->method('unpublish')->with($this->isInstanceOf('Xi\Filelib\File\FileItem'));
        $op->expects($this->never())->method('publish');
        $op->expects($this->any())->method('isReadableByAnonymous')->will($this->returnValue(false));
        $op->expects($this->any())->method('getProfile')->with($this->equalTo('lussenhofer'))->will($this->returnValue($profile));

               
        $op2 = $op->update($file);
        
        $this->assertSame($op, $op2);
        
    }
    
    /**
     * @test
     */
    public function updateShouldDelegateCorrectlyWhenFileCanBePublished()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'publish', 'isReadable', 'isReadableByAnonymous', 'getProfile'))
                   ->getMock();
        
        
        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->once())->method('getLink')->will($this->returnValue('maximuslincitus'));
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $file = $this->getMockBuilder('Xi\Filelib\File\FileItem')
                     ->setMethods(array('setLink'))
                     ->getMock();
        
        $file->setProfile('lussenhofer');
        
        $file->expects($this->once())->method('setLink')->with($this->equalTo('maximuslincitus'));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('updateFile')->with($this->equalTo($file));

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        
        $op->expects($this->once())->method('unpublish')->with($this->isInstanceOf('Xi\Filelib\File\FileItem'));
        $op->expects($this->once())->method('publish')->with($this->isInstanceOf('Xi\Filelib\File\FileItem'));
        $op->expects($this->any())->method('getProfile')->with($this->equalTo('lussenhofer'))->will($this->returnValue($profile));
        $op->expects($this->any())->method('isReadableByAnonymous')->will($this->returnValue(true));

               
        $op->update($file);
        
    }
    
    
    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;
        
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFile')->with($this->equalTo($id))->will($this->returnValue(false));
        
        $filelib->setBackend($backend);
        
        $file = $op->find($id);
        $this->assertEquals(false, $file);
        
    }
    
    
    /**
     * @test
     */
    public function findShouldReturnFileInstanceIfFileIsFound()
    {
        $id = 1;
        
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFile')->with($this->equalTo($id))->will($this->returnValue(
            array(
                'id' => $id,
                'filename' => 'lussen.hof',
            )
        ));
        
        $filelib->setBackend($backend);
        
        $file = $op->find($id);
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);
        $this->assertEquals($id, $file->getId());
        
    }

    
    
    /**
     * @test
     */
    public function findByFilenameShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;
        
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);
        
        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFileByFilename')->with(
            $this->equalTo($folder),
            $this->equalTo($id)
        )->will($this->returnValue(false));
        
        $filelib->setBackend($backend);
        
        $file = $op->findByFilename($folder, $id);
        $this->assertEquals(false, $file);
        
    }
    
    
    /**
     * @test
     */
    public function findByFilenameShouldReturnFileInstanceIfFileIsFound()
    {
        $id = 1;
        
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);
        
        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFileByFilename')->with(
            $this->equalTo($folder),
            $this->equalTo($id)
        )->will($this->returnValue(
            array(
                'id' => $id,
                'filename' => 'lussen.hof',
            )
        ));
        
        $filelib->setBackend($backend);
        
        $file = $op->findByFilename($folder, $id);
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);
        $this->assertEquals($id, $file->getId());
        
    }

    
      /**
     * @test
     */
    public function findAllShouldReturnEmptyIfNoFilesAreFound()
    {
        $id = 1;
        
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findAllFiles')->will($this->returnValue(array()));
        
        $filelib->setBackend($backend);
        
        $files = $op->findAll();
        $this->assertEquals(array(), $files);
        
    }
    
    
    /**
     * @test
     */
    public function findAllShouldReturnAnArrayOfFileInstancesIfFilesAreFound()
    {
                
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findAllFiles')->will($this->returnValue(
            array(
                array(
                    'id' => 1,
                    'name' => 'lussen.hof',
                ),
                array(
                    'id' => 2,
                    'name' => 'lussen.tus',
                ),
                array(
                    'id' => 2,
                    'name' => 'lussen.xoo',
                )
            )
        ));
        
        $filelib->setBackend($backend);
        
        $files = $op->findAll();
        
        $this->assertInternalType('array', $files);
        
        $this->assertCount(3, $files);
        
        $file = $files[1];
        
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', $file);
        
        $this->assertEquals('lussen.tus', $file->getName());
        
    }

    
    /**
     * @test
     */
    public function isReadableByAnonymousShouldDelegateToAcl()
    {
        $filelib = new FileLibrary();
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setMethods(array('getAcl'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();
        
        $file = FileItem::create(array('id' => 1));
        
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->once())->method('isReadableByAnonymous')->with($this->equalTo($file));
        
        $op->expects($this->once())->method('getAcl')->will($this->returnValue($acl));
        
        $op->isReadableByAnonymous($file);
                
    }
    
    /**
     * @test
     */
    public function prepareUploadShouldReturnFileUpload()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);
        
        $upload = $op->prepareUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        
        $this->assertInstanceOf('Xi\Filelib\File\Upload\FileUpload', $upload);
        
    }
    
    
    /**
     * @test
     */
    public function deleteShouldDelegateCorrectly()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'publish', 'isReadable', 'isReadableByAnonymous', 'getProfile'))
                   ->getMock();
                
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        
        $plugin1 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin2 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        
        $file = FileItem::create(array('id' => 1, 'profile' => 'lussen'));
        
        $plugin1->expects($this->once())->method('onDelete')->with($this->equalTo($file));
        $plugin2->expects($this->once())->method('onDelete')->with($this->equalTo($file));
        
        $profile->expects($this->any())->method('getPlugins')->will($this->returnValue(array(
            $plugin1,
            $plugin2,
        )));
        

        

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('deleteFile')->with($this->equalTo($file));

        $storage = $this->getMockForAbstractClass('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('delete')->with($this->equalTo($file));
        
        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        $filelib->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        
        $op->expects($this->once())->method('unpublish')->with($this->isInstanceOf('Xi\Filelib\File\FileItem'));
        
        $op->expects($this->any())->method('getProfile')->with($this->equalTo('lussen'))->will($this->returnValue($profile));
                               
        $op->delete($file);
        
    }
    
    
    public function provideMimetypes()
    {
        return array(
            array('image', 'image/jpeg'),
            array('video', 'video/lus'),
            array('document', 'document/pdf'),
        );
    }
    
    /**
     * @test
     * @dataProvider provideMimetypes
     */
    public function getTypeShouldReturnCorrectType($expected, $mimetype)
    {
        $file = FileItem::create(array('mimetype' => $mimetype));
        
        $filelib = new FileLibrary();
        $op = new DefaultFileOperator($filelib);
                
        $this->assertEquals($expected, $op->getType($file));        
        
    }
    
    /**
     * @test
     */
    public function hasVersionShouldDelegateToProfile()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();
        
         $file = FileItem::create(array('profile' => 'meisterlus'));
         
         $profile = $this->getMock('Xi\Filelib\File\FileProfile');
         $profile->expects($this->once())->method('fileHasVersion')->with($this->equalTo($file), $this->equalTo('kloo'));
         
         $op->expects($this->any())->method('getProfile')->with($this->equalTo('meisterlus'))->will($this->returnValue($profile));
         
         $hasVersion = $op->hasVersion($file, 'kloo');
         
    }
    
    
    /**
     * @test
     */
    public function getVersionProviderShouldDelegateToProfile()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();
        
         $file = FileItem::create(array('profile' => 'meisterlus'));
         
         $profile = $this->getMock('Xi\Filelib\File\FileProfile');
         $profile->expects($this->once())->method('getVersionProvider')->with($this->equalTo($file), $this->equalTo('kloo'));
         
         $op->expects($this->any())->method('getProfile')->with($this->equalTo('meisterlus'))->will($this->returnValue($profile));
         
         $vp = $op->getVersionProvider($file, 'kloo');
         
    }

    
    
    /**
     * @test
     */
    public function publishShouldDelegateCorrectlyWhenProfileAllowsPublicationOfOriginalFile()
    {
        $file = FileItem::create(array('id' => 1, 'profile' => 'lussen'));
        
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'isReadable', 'isReadableByAnonymous', 'getProfile'))
                   ->getMock();
                
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getPublishOriginal')->will($this->returnValue(true));
        
        $plugin1 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin2 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        
        $plugin1->expects($this->once())->method('onPublish')->with($this->equalTo($file));
        $plugin2->expects($this->once())->method('onPublish')->with($this->equalTo($file));
        
        $profile->expects($this->any())->method('getPlugins')->will($this->returnValue(array(
            $plugin1,
            $plugin2,
        )));


        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('publish')->with($this->equalTo($file));
        
        $filelib->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));
        
        $op->expects($this->atLeastOnce())->method('getProfile')->with($this->equalTo('lussen'))->will($this->returnValue($profile));
        
        $op->publish($file);
        
    }

    
    /**
     * @test
     */
    public function publishShouldDelegateCorrectlyWhenProfileDisallowsPublicationOfOriginalFile()
    {
        $file = FileItem::create(array('id' => 1, 'profile' => 'lussen'));
        
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'isReadable', 'isReadableByAnonymous', 'getProfile'))
                   ->getMock();
                
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getPublishOriginal')->will($this->returnValue(false));
        
        $plugin1 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin2 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        
        $plugin1->expects($this->once())->method('onPublish')->with($this->equalTo($file));
        $plugin2->expects($this->once())->method('onPublish')->with($this->equalTo($file));
        
        $profile->expects($this->any())->method('getPlugins')->will($this->returnValue(array(
            $plugin1,
            $plugin2,
        )));


        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->never())->method('publish');
        
        $filelib->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));
        
        $op->expects($this->atLeastOnce())->method('getProfile')->with($this->equalTo('lussen'))->will($this->returnValue($profile));
        
        $op->publish($file);
        
    }

    
    
    /**
     * @test
     */
    public function unpublishShouldDelegateCorrectly()
    {
        $file = FileItem::create(array('id' => 1, 'profile' => 'lussen'));
        
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('publish', 'isReadable', 'isReadableByAnonymous', 'getProfile'))
                   ->getMock();
                
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        
        $plugin1 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin2 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        
        $plugin1->expects($this->once())->method('onUnpublish')->with($this->equalTo($file));
        $plugin2->expects($this->once())->method('onUnpublish')->with($this->equalTo($file));
        
        $profile->expects($this->any())->method('getPlugins')->will($this->returnValue(array(
            $plugin1,
            $plugin2,
        )));

        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('unpublish');
        
        $filelib->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));
        
        $op->expects($this->atLeastOnce())->method('getProfile')->with($this->equalTo('lussen'))->will($this->returnValue($profile));
        
        $op->unpublish($file);
        
    }
    
    /**
     * @test
     */
    public function addProfileShouldDelegateToProfile()
    {
     
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getProfile'))
                   ->getMock();

        
        $plugin = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->atLeastOnce())->method('getProfiles')->will($this->returnValue(array('lussi', 'tussi', 'jussi')));
        
        
        $profile1 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile1->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));
        
        $profile2 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile2->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $profile3 = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile3->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));
        
        $op->expects($this->exactly(3))->method('getProfile')
           ->with($this->logicalOr($this->equalTo('lussi'), $this->equalTo('tussi'), $this->equalTo('jussi')))
           ->will($this->returnValueMap(
                array(
                    array('tussi', $profile1),
                    array('lussi', $profile2),
                    array('jussi', $profile3),
                )
            ));
        
        $op->addPlugin($plugin);
        
    }

    
    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function uploadShouldFailIfAclForbidsUploadToFolder()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getAcl'))
                   ->getMock();
        
        $folder = FolderItem::create(array('id' => 1));
        
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
                
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->atLeastOnce())->method('isWriteable')->with($this->equalTo($folder))->will($this->returnValue(false));
        
        $op->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        
        $op->upload($path, $folder, 'versioned');        
        
    }
    
    
    
    public function provideDataForUploadTest()
    {
        return array(
            array(false, false),
            array(true, true),
        );
    }
    
    
    /**
     * @test
     * @dataProvider provideDataForUploadTest
     */
    public function uploadShouldUploadAndDelegateCorrectly($expectedCallToPublish, $readableByAnonymous)
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('getAcl', 'getProfile', 'getBackend', 'getStorage', 'publish'))
                   ->getMock();
        
        $folder = FolderItem::create(array('id' => 1));
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        
        $plugin1 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin2 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        
        $plugin1->expects($this->once())->method('beforeUpload')
                ->with($this->isInstanceOf('Xi\Filelib\File\Upload\FileUpload'))
                ->will($this->returnArgument(0));
        
        $plugin2->expects($this->once())->method('beforeUpload')
                ->with($this->isInstanceOf('Xi\Filelib\File\Upload\FileUpload'))
                ->will($this->returnArgument(0));

        $plugin1->expects($this->once())->method('afterUpload')
                ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                ->will($this->returnArgument(0));
        
        $plugin2->expects($this->once())->method('afterUpload')
                ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                ->will($this->returnArgument(0));
        
        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->any())->method('getLink')->will($this->returnValue('maximuslincitus'));
        
        $profile->expects($this->any())->method('getPlugins')->will($this->returnValue(array(
            $plugin1,
            $plugin2,
        )));
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('upload')->with($this->isInstanceOf('Xi\Filelib\File\File'));

        $storage = $this->getMockForAbstractClass('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('store')->with($this->isInstanceOf('Xi\Filelib\File\File'));
        
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->atLeastOnce())->method('isWriteable')->with($this->equalTo($folder))->will($this->returnValue(true));
        $acl->expects($this->atLeastOnce())->method('isReadableByAnonymous')->with($this->isInstanceOf('Xi\Filelib\File\File'))->will($this->returnValue($readableByAnonymous));
        
        $op->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        $op->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        $op->expects($this->atLeastOnce())
           ->method('getProfile')
           ->with($this->equalTo('versioned'))
           ->will($this->returnValue($profile));
        
        if ($expectedCallToPublish) {
            $op->expects($this->once())->method('publish')->with($this->isInstanceOf('Xi\Filelib\File\File'));
        } else {
            $op->expects($this->never())->method('publish');
        }
                
        $op->upload($path, $folder, 'versioned');      
        
    }
    
    
    
    
    
}