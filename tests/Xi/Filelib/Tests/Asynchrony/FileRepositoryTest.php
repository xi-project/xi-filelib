<?php

namespace Xi\Filelib\Tests\Asynchrony;

use Prophecy\Prophecy\ObjectProphecy;
use Xi\Filelib\Asynchrony\Asynchrony;
use Xi\Filelib\Asynchrony\ExecutionStrategies;
use Xi\Filelib\Asynchrony\ExecutionStrategy\PekkisQueueExecutionStrategy;
use Xi\Filelib\Asynchrony\FileRepository;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;
use Xi\Filelib\Tests\TestCase;

class FileRepositoryTest extends TestCase
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var ObjectProphecy
     */
    private $repo;

    /**
     * @var Asynchrony
     */
    private $asynchrony;

    public function setUp()
    {
        $this->filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $this->repo = $this->prophesize('Xi\Filelib\File\FileRepositoryInterface');

        $this->filelib->setFileRepository(
            $this->repo->reveal()
        );
        $this->asynchrony = new Asynchrony($this->filelib);
    }

    /**
     * @test
     */
    public function getsStrategy()
    {
        $this->assertEquals(
            ExecutionStrategies::STRATEGY_SYNC,
            $this->filelib->getFileRepository()->getExecutionStrategy(FileRepository::COMMAND_AFTERUPLOAD)
        );
    }

    /**
     * @test
     */
    public function uploads()
    {
        $upload = 'xoox';
        $folder = Folder::create();
        $profile = 'tenhunen';

        $this->repo->upload($upload, $folder, $profile)->shouldBeCalled()->willReturn('jippikaijea');
        $ret = $this->filelib->getFileRepository()->upload($upload, $folder, $profile);
        $this->assertEquals('jippikaijea', $ret);
    }

    /**
     * @test
     */
    public function afterUploads()
    {
        $file = File::create();

        $this->repo->afterUpload($file)->shouldBeCalled()->willReturn('jippikaijea');
        $ret = $this->filelib->getFileRepository()->afterUpload($file);
        $this->assertEquals('jippikaijea', $ret);
    }

    /**
     * @test
     */
    public function copies()
    {
        $file = File::create();
        $folder = Folder::create();

        $this->repo->copy($file, $folder)->shouldBeCalled()->willReturn('jippikaijea');
        $ret = $this->filelib->getFileRepository()->copy($file, $folder);
        $this->assertEquals('jippikaijea', $ret);
    }


    /**
     * @test
     */
    public function deletes()
    {
        $file = File::create();

        $this->repo->delete($file)->shouldBeCalled()->willReturn('jippikaijea');
        $ret = $this->filelib->getFileRepository()->delete($file);
        $this->assertEquals('jippikaijea', $ret);
    }

    /**
     * @test
     */
    public function updates()
    {
        $file = File::create();
        $this->repo->update($file)->shouldBeCalled()->willReturn('jippikaijea');
        $ret = $this->filelib->getFileRepository()->update($file);
        $this->assertEquals('jippikaijea', $ret);
    }

    /**
     * @test
     */
    public function setsStrategy()
    {
        $strategy = $this->prophesize('Xi\Filelib\Asynchrony\ExecutionStrategy\ExecutionStrategy');
        $strategy->attachTo($this->filelib)->shouldBeCalled();
        $strategy->getIdentifier()->shouldBeCalled()->willReturn('xooxer');

        $this->asynchrony->addStrategy(
            $strategy->reveal()
        );

        $this->assertSame(
            $this->filelib->getFileRepository(),
            $this->filelib->getFileRepository()->setExecutionStrategy(
                FileRepository::COMMAND_AFTERUPLOAD,
                'xooxer'
            )
        );

        $this->assertEquals(
            'xooxer',
            $this->filelib->getFileRepository()->getExecutionStrategy(FileRepository::COMMAND_AFTERUPLOAD)
        );
    }

    /**
     * @test
     */
    public function throwsUpOnGettingNonexistantCommand()
    {
        $this->setExpectedException('Xi\Filelib\LogicException');
        $this->filelib->getFileRepository()->getExecutionStrategy('lussutus');
    }

    /**
     * @test
     */
    public function throwsUpOnSettingNonexistantCommand()
    {
        $this->setExpectedException('Xi\Filelib\LogicException');
        $this->filelib->getFileRepository()->setExecutionStrategy(
            'lussutus',
            ExecutionStrategies::STRATEGY_ASYNC_PEKKIS_QUEUE
        );
    }

    /**
     * @test
     */
    public function returnsInnerRepo()
    {
        $this->assertSame($this->repo->reveal(), $this->filelib->getFileRepository()->getInnerRepository());
    }

    /**
     * @test
     */
    public function findDelegates()
    {
        $param = 'xoxo';
        $expected = 'tus';

        $this->repo->find($param)->shouldBeCalled()->willReturn($expected);
        $ret = $this->filelib->getFileRepository()->find($param);
        $this->assertEquals($expected, $ret);
    }

    /**
     * @test
     */
    public function findManyDelegates()
    {
        $param = ['xoxo', 'noxo'];
        $expected = 'tus';

        $this->repo->findMany($param)->shouldBeCalled()->willReturn($expected);
        $ret = $this->filelib->getFileRepository()->findMany($param);
        $this->assertEquals($expected, $ret);
    }

    /**
     * @test
     */
    public function findByDelegates()
    {
        $param = new FileFinder();
        $expected = 'tus';

        $this->repo->findBy($param)->shouldBeCalled()->willReturn($expected);
        $ret = $this->filelib->getFileRepository()->findBy($param);
        $this->assertEquals($expected, $ret);
    }

    /**
     * @test
     */
    public function findByUuidDelegates()
    {
        $param = 'uuid-uuid';
        $expected = 'tus';

        $this->repo->findByUuid($param)->shouldBeCalled()->willReturn($expected);
        $ret = $this->filelib->getFileRepository()->findByUuid($param);
        $this->assertEquals($expected, $ret);
    }


    /**
     * @test
     */
    public function findAllDelegates()
    {
        $expected = 'tus';

        $this->repo->findAll()->shouldBeCalled()->willReturn($expected);
        $ret = $this->filelib->getFileRepository()->findAll();
        $this->assertEquals($expected, $ret);
    }

    /**
     * @test
     */
    public function findByFilenameDelegates()
    {
        $param1 = Folder::create();
        $param2 = 'xooxox';
        $expected = 'tus';

        $this->repo->findByFilename($param1, $param2)->shouldBeCalled()->willReturn($expected);
        $ret = $this->filelib->getFileRepository()->findByFilename($param1, $param2);
        $this->assertEquals($expected, $ret);
    }
}
