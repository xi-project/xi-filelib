<?php

namespace Xi\Tests\Filelib\Folder;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\DefaultFolderOperator;
use Xi\Filelib\Folder\FolderItem;

class DefaultFolderOperatorTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\DefaultFolderOperator'));
    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFolderOperator($filelib);

        $val = 'Lussen\Hofer';
        $this->assertEquals('Xi\Filelib\Folder\FolderItem', $op->getClass());
        $this->assertSame($op, $op->setClass($val));
        $this->assertEquals($val, $op->getClass());
    }

    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolder')->with($this->equalTo($id))->will($this->returnValue(false));

        $filelib->setBackend($backend);

        $folder = $op->find($id);
        $this->assertFalse($folder);
    }

    /**
     * @test
     */
    public function findShouldReturnFolderInstanceIfFileIsFound()
    {
        $id = 1;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolder')->with($this->equalTo($id))->will($this->returnValue(
                        array(
                            'id' => $id,
                            'parent_id' => null,
                        )
                ));

        $filelib->setBackend($backend);

        $folder = $op->find($id);
        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);
        $this->assertEquals($id, $folder->getId());
        $this->assertEquals(null, $folder->getParentId());
    }

    /**
     * @test
     */
    public function findFilesShouldReturnEmptyArrayIteratorWhenNoFilesAreFound()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFilesIn')->with($this->equalTo($folder))->will($this->returnValue(
                        array(
                        )
                ));

        $filelib->setBackend($backend);

        $files = $op->findFiles($folder);

        $this->assertInstanceOf('ArrayIterator', $files);

        $this->assertCount(0, $files);
    }

    /**
     * @test
     */
    public function findFilesShouldReturnNonEmptyArrayIteratorWhenFilesAreFound()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFilesIn')->with($this->equalTo($folder))->will($this->returnValue(
                        array(
                            array('id' => 1, 'mimetype' => 'lus/xoo'),
                            array('id' => 2, 'mimetype' => 'lus/xoo'),
                            array('id' => 3, 'mimetype' => 'lus/tus'),
                        )
                ));

        $filelib->setBackend($backend);

        $files = $op->findFiles($folder);

        $this->assertInstanceOf('ArrayIterator', $files);

        $this->assertCount(3, $files);

        $file = $files->current();

        $this->assertEquals(1, $file->getId());
        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertEquals('lus/xoo', $file->getMimetype());
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFalseWhenParentIdIsNull()
    {
        $id = null;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->never())->method('findFolder');

        $filelib->setBackend($backend);

        $folder = FolderItem::create(array('parent_id' => $id));

        $parent = $op->findParentFolder($folder);

        $this->assertFalse($parent);
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFalseWhenParentIsNotFound()
    {
        $id = 5;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolder')->with($this->equalTo($id))->will($this->returnValue(
                        false
                ));

        $filelib->setBackend($backend);

        $folder = FolderItem::create(array('parent_id' => $id));

        $parent = $op->findParentFolder($folder);

        $this->assertFalse($parent);
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFolderWhenParentIsFound()
    {
        $id = 5;

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolder')->with($this->equalTo($id))->will($this->returnValue(
                        array('id' => 5, 'parent_id' => 6)
                ));

        $filelib->setBackend($backend);

        $folder = FolderItem::create(array('parent_id' => $id));

        $parent = $op->findParentFolder($folder);

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);
    }

    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfConfiguredClassWithNoData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFolderOperator($filelib);

        $mockClass = $this->getMockClass('Xi\Filelib\Folder\FolderItem');

        $folder = $op->getInstance();
        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);
        
        $this->assertSame($filelib, $folder->getFilelib());
        

        $op->setClass($mockClass);

        $folder = $op->getInstance();
        $this->assertInstanceOf($mockClass, $folder);
    }

    /**
     * @test
     */
    public function getInstanceShouldReturnAnInstanceOfConfiguredClassWithData()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $op = new DefaultFolderOperator($filelib);

        $data = array(
            'name' => 'manatee'
        );

        $folder = $op->getInstance($data);
        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);

        $this->assertSame($filelib, $folder->getFilelib());
        $this->assertEquals('manatee', $folder->getName());
    }
    
    
    /**
     * @test
     */
    public function findSubFoldersShouldReturnEmptyArrayIteratorWhenNoSubFoldersAreFound()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findSubFolders')->with($this->equalTo($folder))->will($this->returnValue(
                        array(
                        )
                ));

        $filelib->setBackend($backend);

        $folders = $op->findSubFolders($folder);

        $this->assertInstanceOf('ArrayIterator', $folders);

        $this->assertCount(0, $folders);
    }

    /**
     * @test
     */
    public function findSubFoldersShouldReturnNonEmptyArrayIteratorWhenSubFoldersAreFound()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $folder = FolderItem::create(array('id' => 500, 'parent_id' => 499));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findSubFolders')->with($this->equalTo($folder))->will($this->returnValue(
                        array(
                            array('id' => 433, 'parent_id' => null),
                            array('id' => 24, 'parent_id' => 1),
                            array('id' => 3, 'parent_id' => 2),
                        )
                ));

        $filelib->setBackend($backend);

        $folders = $op->findSubFolders($folder);

        $this->assertInstanceOf('ArrayIterator', $folders);

        $this->assertCount(3, $folders);

        $folders->next();
        $folder = $folders->current();

        $this->assertEquals(24, $folder->getId());
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
    }

    
    /**
     * @test
     */
    public function findByUrlShouldReturnFalseWhenFolderIsNotFound()
    {
        $id = 'lussen/tussi';

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolderByUrl')->with($this->equalTo($id))->will($this->returnValue(
                        false
                ));

        $filelib->setBackend($backend);
        
        $folder = $op->findByUrl($id);

        $this->assertFalse($folder);
    }

    /**
     * @test
     */
    public function findByUrlShouldReturnFolderWhenFolderIsFound()
    {
        $id = 'lussen/tussi';

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findFolderByUrl')->with($this->equalTo($id))->will($this->returnValue(
                        array('url' => 'ussen/tussi', 'id' => 644)
                ));

        $filelib->setBackend($backend);
        
        $folder = $op->findByUrl($id);

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);
        
    }
    
    
    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function findRootShouldFailWhenRootFolderIsNotFound()
    {

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findRootFolder')->will($this->returnValue(
                        false
                ));

        $filelib->setBackend($backend);
        
        $folder = $op->findRoot();

        $this->assertFalse($folder);
    }

    /**
     * @test
     */
    public function findRootShouldReturnFolderWhenRootFolderIsFound()
    {

        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findRootFolder')->will($this->returnValue(
                        array('id' => 1, 'parent_id' => null)
                ));

        $filelib->setBackend($backend);
        
        $folder = $op->findRoot();

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderItem', $folder);
    }

    /**
     * @test
     */
    public function createShouldCreateFolder()
    {
        $filelib = new FileLibrary();
        $op = $this->getMockBuilder('Xi\Filelib\Folder\DefaultFolderOperator')
                   ->setMethods(array('buildRoute'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();
        
        $folder = FolderItem::create(array('id' => 5, 'parent_id' => 1));
        
        $op->expects($this->once())->method('buildRoute')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))->will($this->returnValue('route'));
        
                
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('createFolder')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))->will($this->returnArgument(0));

        $filelib->setBackend($backend);
        
        $folder2 = $op->create($folder);
        
        $this->assertEquals('route', $folder2->getUrl());
        $this->assertSame($filelib, $folder2->getFilelib());
    }
    
    /**
     * @test
     */
    public function deleteShouldDeleteFoldersAndFilesRecursively()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->exactly(4))->method('findSubFolders')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
                ->will($this->returnCallback(function($folder) {
                    
                    if($folder->getId() == 1) {
                        return array(
                            array('id' => 2, 'parent_id' => 1),
                            array('id' => 3, 'parent_id' => 1),
                            array('id' => 4, 'parent_id' => 1),
                        );
                    }
                    return array();
                 }));
        $backend->expects($this->exactly(4))->method('findFilesIn')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
                ->will($this->returnCallback(function($folder) {
                    
                    if($folder->getId() == 4) {
                        return array(
                            array('id' => 1, 'name' => 'tohtori-vesala.avi'),
                            array('id' => 2, 'name' => 'tohtori-vesala.png'),
                            array('id' => 3, 'name' => 'tohtori-vesala.jpg'),
                            array('id' => 4, 'name' => 'tohtori-vesala.bmp'),
                        );
                    }
                    return array();
                 }));
                 
        $backend->expects($this->exactly(4))->method('deleteFolder')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));

        $fiop = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                      ->setMethods(array('delete'))
                      ->setConstructorArgs(array($filelib))
                      ->getMock();
        
        $fiop->expects($this->exactly(4))->method('delete')->with($this->isInstanceOf('Xi\Filelib\File\File'));
                
        $filelib->setBackend($backend); 
        $filelib->setFileOperator($fiop);
        
        $folder = FolderItem::create(array('id' => 1));
        
        $op->delete($folder);
        
    }
    
    /**
     * @test
     */
    public function updateShouldUpdateFoldersAndFilesRecursively()
    {
        $filelib = new FileLibrary();
        $op = $this->getMockBuilder('Xi\Filelib\Folder\DefaultFolderOperator')
                   ->setMethods(array('buildRoute'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();
        
        $op->expects($this->exactly(4))->method('buildRoute')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->exactly(4))->method('findSubFolders')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
                ->will($this->returnCallback(function($folder) {
                    
                    if($folder->getId() == 1) {
                        return array(
                            array('id' => 2, 'parent_id' => 1),
                            array('id' => 3, 'parent_id' => 1),
                            array('id' => 4, 'parent_id' => 1),
                        );
                    }
                    return array();
                 }));
        $backend->expects($this->exactly(4))->method('findFilesIn')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
                ->will($this->returnCallback(function($folder) {
                    
                    if($folder->getId() == 4) {
                        return array(
                            array('id' => 1, 'name' => 'tohtori-vesala.avi'),
                            array('id' => 2, 'name' => 'tohtori-vesala.png'),
                            array('id' => 3, 'name' => 'tohtori-vesala.jpg'),
                            array('id' => 4, 'name' => 'tohtori-vesala.bmp'),
                        );
                    }
                    return array();
                 }));

        $backend->expects($this->exactly(4))->method('updateFolder')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));
                 
        $fiop = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                      ->setMethods(array('update'))
                      ->setConstructorArgs(array($filelib))
                      ->getMock();
        
        $fiop->expects($this->exactly(4))->method('update')->with($this->isInstanceOf('Xi\Filelib\File\File'));
                
        $filelib->setBackend($backend); 
        $filelib->setFileOperator($fiop);
        
        $folder = FolderItem::create(array('id' => 1));
        
        $op->update($folder);
        
    }
    
    
    public function provideDataForBuildRouteTest()
    {
        return array(
            array('lussutus/bansku/tohtori vesala/lamantiini/kaskas/losoboesk', 10),
            array('lussutus/bansku/tohtori vesala/lamantiini/kaskas', 9),
            array('lussutus/bansku/tohtori vesala', 4),
            array('lussutus/bansku/tohtori vesala/lamantiini/klaus kulju', 8),
            array('lussutus/bansku/tohtori vesala/lamantiini/puppe', 6),
        );
    }
    
    
    
    /**
     * @test
     * @dataProvider provideDataForBuildRouteTest
     */
    public function buildRouteShouldBuildBeautifulRoute($expected, $folderId)
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);
        
        // $op->expects($this->exactly(4))->method('buildRoute')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->any())->method('findFolder')
                ->will($this->returnCallback(function($folderId) {

                    $farr = array(
                        1 => array('parent_id' => null, 'name' => 'root'),
                        2 => array('parent_id' => 1, 'name' => 'lussutus'),
                        3 => array('parent_id' => 2, 'name' => 'bansku'),
                        4 => array('parent_id' => 3, 'name' => 'tohtori vesala'),
                        5 => array('parent_id' => 4, 'name' => 'lamantiini'),
                        6 => array('parent_id' => 5, 'name' => 'puppe'),
                        7 => array('parent_id' => 6, 'name' => 'nilkki'),
                        8 => array('parent_id' => 5, 'name' => 'klaus kulju'),
                        9 => array('parent_id' => 5, 'name' => 'kaskas'),
                        10 => array('parent_id' => 9, 'name' => 'losoboesk')
                    );
                    
                    if (isset($farr[$folderId])) {
                        return $farr[$folderId];
                    }

                    return false;
                    
                 }));
        
        $filelib->setBackend($backend);
                 
        $folder = $op->find($folderId);
        
        $route = $op->buildRoute($folder);
        
        $this->assertEquals($expected, $route);
        
        
    }
    
    
    /**
     * @test
     */
    public function createByUrlShouldExitEarlyIfFolderExists()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        
        $backend->expects($this->never())->method('findRoot');
        
        $backend->expects($this->once())->method('findFolderByUrl')->with($this->equalTo('tussin/lussutus/festivaali/2010'))
                ->will($this->returnValue(array('id' => 666, 'parent_id' => 555)));
        
        
        $filelib->setBackend($backend);
                 
        $folder = $op->createByUrl('tussin/lussutus/festivaali/2010');
        
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals(666, $folder->getId());
        
    }
    

    
    /**
     * @test
     */
    public function createByUrlShouldCreateRecursivelyIfFolderDoesNotExist()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findRootFolder')->will($this->returnValue(array('id' => 1, 'name' => 'root')));
        
        
        $backend->expects($this->exactly(4))->method('createFolder')->will($this->returnCallback(function($folder) {
            static $count = 1;
            $folder->setId($count++);
            return $folder;
        }));
        
        
        $backend->expects($this->exactly(5))->method('findFolderByUrl')->will($this->returnValue(false));
                
        $filelib->setBackend($backend);
                 
        $folder = $op->createByUrl('tussin/lussutus/festivaali/2012');
        
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals('2012', $folder->getName());
        
    }

    
    /**
     * @test
     */
    public function createByUrlShouldCreateRecursivelyFromTheMiddleIfSomeFoldersExist()
    {
        $filelib = new FileLibrary();
        $op = new DefaultFolderOperator($filelib);
        
        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('findRootFolder')->will($this->returnValue(array('id' => 1, 'name' => 'root')));
        
        
        $backend->expects($this->exactly(2))->method('createFolder')->will($this->returnCallback(function($folder) {
            static $count = 1;
            $folder->setId($count++);
            return $folder;
        }));
                
        $backend->expects($this->exactly(5))->method('findFolderByUrl')->will($this->returnCallback(function($url) {
        
            if ($url === 'tussin') {
                return array('id' => 536, 'parent_id' => 545, 'url' => 'tussin');
            }

            if ($url === 'tussin/lussutus') {
                return array('id' => 537, 'parent_id' => 5476, 'url' => 'tussin/lussutus');
            }
            
            return false;
            
        }));
                
        $filelib->setBackend($backend);
                 
        $folder = $op->createByUrl('tussin/lussutus/festivaali/2012');
        
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals('2012', $folder->getName());
        
    }
}