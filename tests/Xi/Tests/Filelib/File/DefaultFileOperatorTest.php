<?php

namespace Xi\Tests\Filelib\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\DefaultFileOperator;

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
                   ->setMethods(array('unpublish', 'publish', 'isReadable', 'isReadableByAnonymous'))
                   ->getMock();
        
        
        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->once())->method('getLink')->will($this->returnValue('maximuslincitus'));
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $file = $this->getMock('Xi\Filelib\File\FileItem');
        $file->expects($this->any())->method('getProfileObject')->will($this->returnValue($profile));
        $file->expects($this->once())->method('setLink')->with($this->equalTo('maximuslincitus'));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('updateFile')->with($this->equalTo($file));

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        
        $op->expects($this->once())->method('unpublish')->with($this->isInstanceOf('Xi\Filelib\File\FileItem'));
        $op->expects($this->never())->method('publish');
        $op->expects($this->any())->method('isReadableByAnonymous')->will($this->returnValue(false));

               
        $op->update($file);
        
    }
    
    /**
     * @test
     */
    public function updateShouldDelegateCorrectlyWhenFileCanBePublished()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                   ->setConstructorArgs(array($filelib))
                   ->setMethods(array('unpublish', 'publish', 'isReadable', 'isReadableByAnonymous'))
                   ->getMock();
        
        
        $linker = $this->getMock('Xi\Filelib\Linker\Linker');
        $linker->expects($this->once())->method('getLink')->will($this->returnValue('maximuslincitus'));
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->any())->method('getLinker')->will($this->returnValue($linker));

        $file = $this->getMock('Xi\Filelib\File\FileItem');
        $file->expects($this->any())->method('getProfileObject')->will($this->returnValue($profile));
        $file->expects($this->once())->method('setLink')->with($this->equalTo('maximuslincitus'));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('updateFile')->with($this->equalTo($file));

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        
        $op->expects($this->once())->method('unpublish')->with($this->isInstanceOf('Xi\Filelib\File\FileItem'));
        $op->expects($this->once())->method('publish')->with($this->isInstanceOf('Xi\Filelib\File\FileItem'));
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

    
}