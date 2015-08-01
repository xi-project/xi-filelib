<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\File;

use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Events;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\RandomizeNamePlugin;
use Xi\Filelib\Profile\FileProfile;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;

class FileRepositoryTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $backend;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ed;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $foop;

    /**
     * @var FileRepository
     */
    private $op;

    public function setUp()
    {
        $this->ed = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->filelib = $this->getFilelib(true);

        $this->op = $this->filelib->getFileRepository();
    }

    private function getFilelib($mockedEventDispatcher)
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter(),
            ($mockedEventDispatcher) ? $this->ed->reveal() : new EventDispatcher()
        );

        $filelib->addProfile(new FileProfile(
            'tussi', false
        ));

        return $filelib;
    }

    public function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('files');
        $deletor->delete();
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\File\FileRepository');
    }

    public function provideUploads()
    {
        return array(
            array(new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg')),
            array(ROOT_TESTS . '/data/self-lussing-manatee.jpg')
        );
    }

    /**
     * @test
     * @dataProvider provideUploads
     */
    public function uploads($upload)
    {
        $folder = $this->filelib->createFolderByUrl('xooxer/looxer');

        $file = $this->filelib->getFileRepository()->upload($upload, $folder, 'default');

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertEquals(File::STATUS_RAW, $file->getStatus());
        $this->assertUuid($file->getId());
        $this->assertUuid($file->getUuid());
        $this->assertEquals('default', $file->getProfile());
        $this->assertInstanceof('DateTime', $file->getDateCreated());
        $this->assertEquals($folder->getId(), $file->getFolderId());

        $this->ed->dispatch(
            Events::RESOURCE_BEFORE_CREATE,
            Argument::type('Xi\Filelib\Event\ResourceEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FOLDER_BEFORE_WRITE_TO,
            Argument::type('Xi\Filelib\Event\FolderEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FILE_UPLOAD,
            Argument::type('Xi\Filelib\Event\FileUploadEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FILE_BEFORE_CREATE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FILE_AFTER_CREATE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

        $this->assertTrue($this->filelib->getStorage()->exists($file->getResource()));

    }

    /**
     * @test
     * @dataProvider provideUploads
     */
    public function uploadShouldFindRootFolderIfNoFolderIsSupplied($upload)
    {
        $file = $this->filelib->getFileRepository()->upload($upload, null);
        $this->assertInstanceOf('Xi\Filelib\File\File', $file);

        $this->assertEquals(
            $this->filelib->getFolderRepository()->findRoot()->getId(),
            $file->getFolderId()
        );
    }

    /**
     * @test
     */
    public function afterUploadDispatches()
    {
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->getFileRepository()->upload($upload, null);

        $file2 = $this->filelib->getFileRepository()->afterUpload($file);

        $this->assertSame($file, $file2);

        $this->ed->dispatch(
            Events::FILE_AFTER_AFTERUPLOAD,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FILE_AFTER_UPDATE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

        $this->assertEquals(File::STATUS_COMPLETED, $file->getStatus());
    }

    /**
     * @test
     */
    public function findShouldReturnFalseIfFileIsNotFound()
    {
        $id = 'xooxoox';
        $file = $this->op->find($id);
        $this->assertEquals(false, $file);
    }

    /**
     * @test
     */
    public function findShouldReturnFileInstanceIfFileIsFound()
    {
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->getFileRepository()->upload($upload, null);
        $ret = $this->op->find($file->getId());
        $this->assertEquals($file, $ret);
    }

    /**
     * @test
     */
    public function findManyDelegatesToBackend()
    {
        $filelib = $this->getFilelib(false);
        $filelib->addPlugin(new RandomizeNamePlugin());

        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $filelib->getFileRepository()->upload($upload, null);
        $file2 = $filelib->getFileRepository()->upload($upload, null);

        $ret = $filelib->getFileRepository()->findMany([$file->getId(), $file2->getId()]);
        $this->assertCount(2, $ret);
    }


    /**
     * @test
     */
    public function findByFilenameShouldReturnFalseIfFileIsNotFound()
    {
        $folder = $this->filelib->createFolderByUrl('tussen/lussen');
        $ret = $this->op->findByFilename($folder, 'lussname');
        $this->assertFalse($ret);
    }

    /**
     * @test
     */
    public function findsByUuid()
    {
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->getFileRepository()->upload($upload, null);

        $ret = $this->op->findByUuid('tenhusen-hubriksen-uuid');
        $this->assertFalse($ret);

        $ret2 = $this->op->findByUuid($file->getUuid());
        $this->assertInstanceOf('Xi\Filelib\File\File', $ret2);
    }


    /**
     * @test
     */
    public function findByFilenameShouldReturnFileInstanceIfFileIsFound()
    {
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $folder = $this->filelib->getFolderRepository()->createByUrl('arto/tenhusen/suuruuden/ylistyskansio');
        $file = $this->filelib->getFileRepository()->upload($upload, $folder);

        $ret = $this->op->findByFilename($folder, 'self-lussing-manatee.jpg');
        $this->assertEquals($file, $ret);
    }

    /**
     * @test
     * @group tussi
     */
    public function findAllShouldReturnEmptyIfNoFilesAreFound()
    {
        $files = $this->op->findAll();
        $this->assertCount(0, $files);
    }

    /**
     * @test
     */
    public function findAllShouldReturnAnArrayOfFileInstancesIfFilesAreFound()
    {
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $this->filelib->getFileRepository()->upload(
            $upload,
            $this->filelib->getFolderRepository()->createByUrl('arto-tenhunen')
        );
        $this->filelib->getFileRepository()->upload(
            $upload,
            $this->filelib->getFolderRepository()->createByUrl('arto-tenhunen/on')
        );
        $this->filelib->getFileRepository()->upload(
            $upload,
            $this->filelib->getFolderRepository()->createByUrl('arto-tenhunen/on/suurmies')
        );

        $files = $this->op->findAll();
        $this->assertCount(3, $files);
    }

    /**
     * @test
     * @group luszo
     */
    public function updates()
    {
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->getFileRepository()->upload($upload, null);

        $this->filelib->getFileRepository()->update($file);

        $this->ed->dispatch(
            Events::FILE_BEFORE_UPDATE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FILE_AFTER_UPDATE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

    }

    /**
     * @test
     * @group luszo
     */
    public function copies()
    {
        return $this->markTestSkipped('Hangs for the moment');

        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->getFileRepository()->upload($upload, null);
        $this->filelib->getFileRepository()->afterUpload($file);

        $folder = $this->filelib->createFolderByUrl('tussen/lussen/luu');

        $file2 = $this->filelib->getFileRepository()->copy($file, $folder);
        $this->assertInstanceof('Xi\Filelib\File\File', $file2);
        $this->assertNotSame($file, $file2);
        $this->assertNotEquals($file->getFolderId(), $file2->getFolderId());

        $this->assertEquals($file2->getFolderId(), $folder->getId());

        $this->ed->dispatch(
            Events::FILE_BEFORE_COPY,
            Argument::type('Xi\Filelib\Event\FileCopyEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FILE_AFTER_CREATE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FILE_AFTER_COPY,
            Argument::type('Xi\Filelib\Event\FileCopyEvent')
        )->shouldHaveBeenCalled();
    }

    /**
     * @test
     * @group luszo
     */
    public function copiesMultipleTimes()
    {
        return $this->markTestSkipped('Hangs for the moment');

        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->getFileRepository()->upload($upload, null);
        $this->filelib->getFileRepository()->afterUpload($file);

        $folder = $this->filelib->createFolderByUrl('tussen/lussen/luu');
        $file2 = $this->filelib->getFileRepository()->copy($file, $folder);

        $file3 = $this->filelib->getFileRepository()->copy($file, $folder);
        $file4 = $this->filelib->getFileRepository()->copy($file, $folder);

        $file5 = $this->filelib->getFileRepository()->copy($file4, $folder);
        $file6 = $this->filelib->getFileRepository()->copy($file5, $folder);


        $this->assertEquals('self-lussing-manatee.jpg', $file2->getName());
        $this->assertEquals('self-lussing-manatee copy 4.jpg', $file6->getName());

        $this->assertSame($file->getResource(), $file6->getResource());
    }

    /**
     * @test
     * @group luszo
     */
    public function createsNewResourceWhenCopyingIfExclusive()
    {
        return $this->markTestSkipped('Hangs for the moment');
        
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->getFileRepository()->upload($upload, null, 'tussi');
        $this->filelib->getFileRepository()->afterUpload($file);

        $folder = $this->filelib->createFolderByUrl('exclusivo');
        $file2 = $this->filelib->getFileRepository()->copy($file, $folder);

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertTrue($file->getResource()->isExclusive());
        $this->assertNotSame($file->getResource(), $file2->getResource());
    }


    /**
     * @test
     * @group luszo
     */
    public function deletes()
    {
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->getFileRepository()->upload($upload, null);

        $this->filelib->getFileRepository()->delete($file);

        $this->ed->dispatch(
            Events::FILE_BEFORE_DELETE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

        $this->ed->dispatch(
            Events::FILE_AFTER_DELETE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();

        $this->assertEquals(File::STATUS_DELETED, $file->getStatus());
        $this->assertFalse($this->filelib->getFileRepository()->find($file->getId()));
        $this->assertTrue($this->filelib->getStorage()->exists($file->getResource()));
    }

    /**
     * @test
     * @group luszo
     */
    public function deletesExclusiveResource()
    {
        $upload = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $file = $this->filelib->getFileRepository()->upload($upload, null, 'tussi');
        $this->filelib->getFileRepository()->delete($file);
        $this->ed->dispatch(
            Events::FILE_AFTER_DELETE,
            Argument::type('Xi\Filelib\Event\FileEvent')
        )->shouldHaveBeenCalled();
        $this->assertFalse($this->filelib->getStorage()->exists($file->getResource()));
    }

}
