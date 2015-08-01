<?php

namespace Xi\Filelib\Tests\Folder;

use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Events;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Backend\Finder\FolderFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use PhpCollection\Sequence;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;

class FolderRepositoryTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var FolderRepository
     */
    private $op;

    public function setUp()
    {
        $this->ed = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->filelib = $this->getFilelib(true);

        $this->op = $this->filelib->getFolderRepository();
    }

    /**
     * @param bool $mockedEventDispatcher
     * @return FileLibrary
     */
    private function getFilelib($mockedEventDispatcher)
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter(),
            ($mockedEventDispatcher) ? $this->ed->reveal() : new EventDispatcher()
        );

        return $filelib;
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
    public function findReturnsFalseWhenNotFound()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $this->assertFalse($op->find('xoo-xoo'));
    }

    /**
     * @test
     */
    public function findReturnsFolder()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $folder = $op->createByUrl('lubbo');

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $op->find($folder->getId()));
    }

    /**
     * @test
     */
    public function findsFiles()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $folder = $op->createByUrl('laa-laa/pai/isohali');

        $filelib->uploadFile(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg',
            $folder
        );

        $this->assertCount(1, $op->findFiles($folder));
    }

    /**
     * @test
     */
    public function findsParentFolder()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $root = $op->findRoot();
        $this->assertFalse($op->findParentFolder($root));

        $folder = $op->create(Folder::create([
            'name' => 'lipaiseppa-manaattia',
            'parent_id' => $root->getId()
        ]));

        $this->assertEquals($root, $op->findParentFolder($folder));
    }

    /**
     * @test
     */
    public function findsSubFolders()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $folder = $op->createByUrl('sopranon/tuloksen/tappion/ylistys');

        $this->assertContains(
            $folder,
            $op->findSubFolders($op->findByUrl('sopranon/tuloksen/tappion'))
        );
    }

    /**
     * @test
     */
    public function findsCorrectSubFolders()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $folder1 = $op->createByUrl('tenhusen/suuruus');
        $folder2 = $op->createByUrl('tenhusen/pienuus/on-suurta-sekin');
        $folder3 = $op->createByUrl('tenhusen/pienuus/on-valetta');


        $this->assertCount(0, $op->findSubFolders($op->findByUrl('tenhusen/suuruus')));
        $this->assertCount(2, $op->findSubFolders($op->findByUrl('tenhusen/pienuus')));

    }

    /**
     * @test
     */
    public function findsByUrl()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $this->assertFalse($op->findByUrl('banaani/ei-ole/banaani'));
        $op->createByUrl('banaani/ei-ole/banaani');

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $op->findByUrl('banaani/ei-ole/banaani'));
    }

    /**
     * @test
     * @group tissu
     */
    public function findsRoot()
    {
        $root = $this->op->findRoot();
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $root);

        $this->assertEquals('root', $root->getUrl());
        $this->assertEquals('root', $root->getName());
    }


    /**
     * @test
     */
    public function creates()
    {
        $root = $this->op->findRoot();

        $folder = Folder::create(
            [
                'name' => 'tussi',
                'parent_id' => $root->getId()
            ]
        );

        $this->op->create($folder);

        $this->ed->dispatch(
            Events::FOLDER_BEFORE_WRITE_TO,
            Argument::type('Xi\Filelib\Event\FolderEvent')
        )->shouldHaveBeenCalledTimes(1);

        $this->ed->dispatch(
            Events::FOLDER_BEFORE_CREATE,
            Argument::type('Xi\Filelib\Event\FolderEvent')
        )->shouldHaveBeenCalledTimes(2);

        $this->ed->dispatch(
            Events::FOLDER_AFTER_CREATE,
            Argument::type('Xi\Filelib\Event\FolderEvent')
        )->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @test
     */
    public function creatingRootLevelFolderFails()
    {
        $folder = Folder::create(
            [
                'name' => 'krook',
                'parent_id' => null
            ]
        );

        $this->setExpectedException('Xi\Filelib\LogicException');
        $this->op->create($folder);
    }

    /**
     * @test
     * @group tissu
     */
    public function deletes()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $root = $op->findRoot();

        $folder = Folder::create(
            [
                'name' => 'tussi',
                'parent_id' => $root->getId()
            ]
        );

        $folder2 = $op->create($folder);

        $folder3 = $op->create(Folder::create([
            'name' => 'watussi',
            'parent_id' => $folder2->getId()
        ]));

        $this->assertNotFalse($op->findByUrl('tussi/watussi'));

        $file = $filelib->uploadFile(ROOT_TESTS . '/data/self-lussing-manatee.jpg', $folder3);

        $this->assertEquals(File::STATUS_COMPLETED, $file->getStatus());

        $op->delete($folder);

        $this->assertEquals(File::STATUS_DELETED, $file->getStatus());

        $this->assertFalse($op->findByUrl('tussi/watussi'));
    }

    /**
     * @test
     */
    public function updates()
    {
        $root = $this->op->findRoot();

        $folder = Folder::create(
            [
                'name' => 'tussi',
                'parent_id' => $root->getId()
            ]
        );

        $folder2 = $this->op->create($folder);

        $this->assertSame($folder, $folder2);

        $this->op->update($folder);

        $this->ed->dispatch(
            Events::FOLDER_AFTER_UPDATE,
            Argument::type('Xi\Filelib\Event\FolderEvent')
        )->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @test
     */
    public function updatesRecursively()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $root = $op->findRoot();

        $folder = Folder::create(
            [
                'name' => 'tussi',
                'parent_id' => $root->getId()
            ]
        );

        $folder2 = $op->create($folder);

        $folder3 = $op->create(Folder::create([
            'name' => 'watussi',
            'parent_id' => $folder2->getId()
        ]));

        $this->assertEquals('tussi/watussi', $folder3->getUrl());

        $file = $filelib->uploadFile(ROOT_TESTS . '/data/self-lussing-manatee.jpg', $folder3);

        $folder->setName('xooxer');

        $op->update($folder);

        $this->assertEquals('xooxer/watussi', $folder3->getUrl());
    }

    /**
     * @test
     */
    public function createsByUrl()
    {
        $filelib = $this->getFilelib(false);
        $op = $filelib->getFolderRepository();

        $folder = $op->createByUrl('tenhunen/imaisee/mehevaa');
        $folder2 = $op->createByUrl('tenhunen/imaisee/mehevaa/ankkaa');

        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder);
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', $folder2);

        $this->assertUuid($folder->getUuid());

        $this->assertEquals('tenhunen/imaisee/mehevaa', $folder->getUrl());
        $this->assertEquals('tenhunen/imaisee/mehevaa/ankkaa', $folder2->getUrl());
        $this->assertEquals($folder->getId(), $folder2->getParentId());

        $folder3 = $op->createByUrl('tenhunen/imaisee/mehevaa');
        $this->assertSame($folder, $folder3);
    }



}
