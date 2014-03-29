<?php

namespace Xi\Filelib\Tests\Folder;

use Xi\Filelib\Command\Commander;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;
use ArrayIterator;

class FolderRepositoryTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filelib;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $backend;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $commander;

    /**
     * @var FolderRepository
     */
    private $op;

    public function setUp()
    {
        $this->commander = $this->getMockedCommander();

        $this->backend = $this->getMockedBackend();
        $this->filelib = $this->getMockedFilelib(null, null, null, null, null, $this->backend, $this->commander);
        $this->op = new FolderRepository();
        $this->op->attachTo($this->filelib);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Folder\FolderRepository');
    }

    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 1;

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with($id, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue(false));

        $folder = $this->op->find($id);
        $this->assertFalse($folder);
    }

    /**
     * @test
     */
    public function findShouldReturnFolderInstanceIfFileIsFound()
    {
        $id = 1;

        $folder = new Folder();

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with($id, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue($folder));

        $ret = $this->op->find($id);
        $this->assertSame($folder, $ret);
    }

    /**
     * @test
     */
    public function findFilesShouldReturnEmptyArrayIteratorWhenNoFilesAreFound()
    {
        $finder = new FileFinder(
            array(
                'folder_id' => 500,
            )
        );

        $folders = new ArrayIterator(array());

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue($folders));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));
        $files = $this->op->findFiles($folder);

        $this->assertInstanceOf('ArrayIterator', $files);
        $this->assertCount(0, $files);
    }

    /**
     * @test
     */
    public function findFilesShouldReturnNonEmptyArrayIteratorWhenFilesAreFound()
    {
        $finder = new FileFinder(
            array(
                'folder_id' => 500,
            )
        );

        $files = new ArrayIterator(
            array(
                new File(),
                new File(),
                new File(),
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($files));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));
        $files = $this->op->findFiles($folder);

        $this->assertInstanceOf('ArrayIterator', $files);
        $this->assertCount(3, $files);

    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFalseWhenParentIdIsNull()
    {
        $id = null;

        $this->backend->expects($this->never())->method('findById');

        $folder = Folder::create(array('parent_id' => $id));
        $parent = $this->op->findParentFolder($folder);
        $this->assertFalse($parent);
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFalseWhenParentIsNotFound()
    {
        $id = 5;

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with(5, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue(false));

        $folder = Folder::create(array('parent_id' => $id));

        $parent = $this->op->findParentFolder($folder);
        $this->assertFalse($parent);
    }

    /**
     * @test
     */
    public function findParentFolderShouldReturnFolderWhenParentIsFound()
    {
        $id = 5;
        $parentFolder = new Folder();

        $this->backend
            ->expects($this->once())
            ->method('findById')
            ->with(5, 'Xi\Filelib\Folder\Folder')
            ->will($this->returnValue($parentFolder));

        $folder = Folder::create(array('parent_id' => $id));

        $ret = $this->op->findParentFolder($folder);
        $this->assertSame($parentFolder, $ret);
    }

    /**
     * @test
     */
    public function findSubFoldersShouldReturnEmptyArrayIteratorWhenNoSubFoldersAreFound()
    {
        $finder = new FolderFinder(
            array(
                'parent_id' => 500,
            )
        );

        $folders = new ArrayIterator(array());

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue($folders));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));
        $files = $this->op->findSubFolders($folder);

        $this->assertInstanceOf('ArrayIterator', $files);
        $this->assertCount(0, $files);
    }

    /**
     * @test
     */
    public function findSubFoldersShouldReturnNonEmptyArrayIteratorWhenSubFoldersAreFound()
    {
       $finder = new FolderFinder(
            array(
                'parent_id' => 500,
            )
        );

        $folders = new ArrayIterator(
            array(
                new Folder(),
                new Folder(),
                new Folder(),
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($folders));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));
        $files = $this->op->findSubFolders($folder);

        $this->assertInstanceOf('ArrayIterator', $files);
        $this->assertCount(3, $files);
    }

    /**
     * @test
     */
    public function findByUrlShouldReturnFolderWhenFolderIsFound()
    {
        $finder = new FolderFinder(
            array(
                'url' => 'lussen/tussi',
            )
        );

        $folders = new ArrayIterator(
            array(
                new Folder(),
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')
            ->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue($folders));

        $folder = Folder::create(array('id' => 500, 'parent_id' => 499));

        $id = 'lussen/tussi';

        $folder = $this->op->findByUrl($id);
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
    }

    /**
     * @test
     * @group luzzo
     */
    public function findRootShouldCreateRootWhenItIsNotFound()
    {
        $command = $this->getMockedCommand();
        $command
            ->expects($this->once())
            ->method('execute');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FolderRepository::COMMAND_CREATE
            )
            ->will($this->returnValue($command));

        $finder = new FolderFinder(
            array(
                'parent_id' => null,
            )
        );

        $folders = new ArrayIterator(
            array(
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
                $this->equalTo($finder)
            )
            ->will($this->returnValue($folders));


        $folder = $this->op->findRoot();
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertEquals('root', $folder->getName());
        $this->assertNull($folder->getParentId());
    }

    /**
     * @test
     */
    public function findRootShouldReturnFolderWhenRootFolderIsFound()
    {
        $finder = new FolderFinder(
            array(
                'parent_id' => null,
            )
        );

        $folders = new ArrayIterator(
            array(
                new Folder(),
            )
        );

        $this->backend
            ->expects($this->once())
            ->method('findByFinder')->with(
            $this->equalTo($finder)
        )
            ->will($this->returnValue($folders));

        $folder = $this->op->findRoot();
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
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
        $backend = $this->getMockedBackend();
        $filelib = $this->getMockedFilelib(null, null, null, null, null, $backend);
        $op = new FolderRepository();
        $op->attachTo($filelib);

        // $op->expects($this->exactly(4))->method('buildRoute')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));

        $backend->expects($this->any())
                ->method('findById')
                ->with($this->isType('int'), 'Xi\Filelib\Folder\Folder')
                ->will($this->returnCallback(function($folderId, $class) {

                    $farr = array(
                        1 => Folder::create(array('parent_id' => null, 'name' => 'root')),
                        2 => Folder::create(array('parent_id' => 1, 'name' => 'lussutus')),
                        3 => Folder::create(array('parent_id' => 2, 'name' => 'bansku')),
                        4 => Folder::create(array('parent_id' => 3, 'name' => 'tohtori vesala')),
                        5 => Folder::create(array('parent_id' => 4, 'name' => 'lamantiini')),
                        6 => Folder::create(array('parent_id' => 5, 'name' => 'puppe')),
                        7 => Folder::create(array('parent_id' => 6, 'name' => 'nilkki')),
                        8 => Folder::create(array('parent_id' => 5, 'name' => 'klaus kulju')),
                        9 => Folder::create(array('parent_id' => 5, 'name' => 'kaskas')),
                        10 => Folder::create(array('parent_id' => 9, 'name' => 'losoboesk'))
                    );

                    if (isset($farr[$folderId])) {
                        return $farr[$folderId];
                    }

                    return false;
                }
            )
        );

        $folder = $op->find($folderId);

        $route = $op->buildRoute($folder);

        $this->assertEquals($expected, $route);
    }

    /**
     * @test
     */
    public function createCreatesExecutableAndExecutes()
    {
        $folder = Folder::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FolderRepository::COMMAND_CREATE,
                array(
                    $folder
                )
            )
            ->will($this->returnValue($command));


        $this->op->create($folder);
    }

    /**
     * @test
     */
    public function deleteCreatesExecutableAndExecutes()
    {
        $folder = Folder::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FolderRepository::COMMAND_DELETE,
                array(
                    $folder
                )
            )
            ->will($this->returnValue($command));


        $this->op->delete($folder);
    }

    /**
     * @test
     */
    public function updateCreatesExecutableAndExecutes()
    {
        $folder = Folder::create();
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FolderRepository::COMMAND_UPDATE,
                array(
                    $folder
                )
            )
            ->will($this->returnValue($command));


        $this->op->update($folder);
    }

    /**
     * @test
     */
    public function createByUrlCreatesExecutableAndExecutes()
    {
        $url = 'arto/tenhunen/on/losonaama/ja/imaisee/mehevaa';
        $command = $this->getMockedCommand('topic', 'xoo');

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with(
                FolderRepository::COMMAND_CREATE_BY_URL,
                array(
                    $url
                )
            )
            ->will($this->returnValue($command));


        $this->op->createByUrl($url);
    }

}
