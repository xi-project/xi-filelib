<?php

namespace Xi\Filelib\Tests\Publisher;

use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\Publisher\Events;

class PublisherTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fiop;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $linker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ed;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $profile;

    public function setUp()
    {

        $this->profile = $this->getMockedFileProfile('default');
        $this->profile
            ->expects($this->any())
            ->method('getFileVersions')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(array('ankan', 'imaisu')));

        $this->fiop = $this->getMockedFileOperator();
        $this->fiop
            ->expects($this->any())
            ->method('getProfile')
            ->with('default')
            ->will($this->returnValue($this->profile));

        $this->provider = $this->getMockedVersionProvider('lipsautus');

        $this->fiop
            ->expects($this->any())
            ->method('getVersionProvider')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'), $this->logicalOr('ankan', 'imaisu'))
            ->will($this->returnValue($this->provider));

        $this->ed = $this->getMockedEventDispatcher();

        $filelib = $this->getMockedFilelib(null, $this->fiop, null, null, $this->ed);


        $this->adapter = $this->getMock('Xi\Filelib\Publisher\PublisherAdapter');
        $this->adapter->expects($this->once())->method('attachTo')->with($filelib);

        $this->linker = $this->getMockedLinker();
        $this->linker->expects($this->once())->method('attachTo')->with($filelib);

        $this->publisher = new Publisher($this->adapter, $this->linker);
        $this->publisher->attachTo($filelib);
    }

    /**
     * @test
     */
    public function shouldSubscribeToEvents()
    {
        $expected = array(
            CoreEvents::FILE_BEFORE_DELETE => array('onBeforeDelete')
        );

        $this->assertEquals(
            $expected, Publisher::getSubscribedEvents()
        );
    }

    /**
     * @test
     */
    public function onBeforeDeleteShouldUnpublish()
    {
        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Publisher')
            ->setMethods(array('unpublish', 'publish'))
            ->disableOriginalConstructor()
            ->getMock();

        $file = File::create();

        $publisher->expects($this->once())->method('unpublish')->with($file);

        $event = new FileEvent($file);
        $publisher->onBeforeDelete($event);
    }

    /**
     * @test
     */
    public function isPublishedShouldReferFileData()
    {
        $file = File::create();
        $this->assertFalse($this->publisher->isPublished($file));

        $data = $file->getData();
        $this->assertFalse($this->publisher->isPublished($file));

        $data['publisher.published'] = 0;
        $this->assertFalse($this->publisher->isPublished($file));

        $data['publisher.published'] = 1;
        $this->assertTrue($this->publisher->isPublished($file));
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Publisher\Publisher');
    }

    /**
     * @test
     */
    public function getUrlShouldDelegateToAdapterIfNoCachedData()
    {
        $file = File::create();

        $this->adapter
            ->expects($this->once())
            ->method('getUrlVersion')
            ->with(
                $file,
                'ankan',
                $this->provider,
                $this->linker
            )
            ->will($this->returnValue('lussutusbansku'));

        $this->fiop
            ->expects($this->once())
            ->method('update')
            ->with($file);

        $ret = $this->publisher->getUrlVersion($file, 'ankan');
        $this->assertEquals('lussutusbansku', $ret);

        $data = $file->getData();
        $this->assertEquals('lussutusbansku', $data['publisher.version_url']['ankan']);
    }

    /**
     * @test
     */
    public function getUrlShouldUseCachedDataWhenAvailable()
    {
        $file = File::create();
        $data = $file->getData();
        $data['publisher.version_url']['ankan'] = 'kerran-tenhusen-lipaisema-lopullisesti-pilalla';

        $this->adapter
            ->expects($this->never())
            ->method('getUrlVersion');

        $this->fiop
            ->expects($this->never())
            ->method('update');

        $ret = $this->publisher->getUrlVersion($file, 'ankan');
        $this->assertEquals('kerran-tenhusen-lipaisema-lopullisesti-pilalla', $ret);
    }


    /**
     * @test
     */
    public function publishShouldPublish()
    {
        $file = File::create(array('profile' => 'default'));

        $this->fiop->expects($this->once())->method('update')->with($file);

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FILE_BEFORE_PUBLISH, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));


        $this->adapter
            ->expects($this->at(0))
            ->method('publish')
            ->with(
                $file,
                'ankan',
                $this->provider,
                $this->linker
            );

        $this->adapter
            ->expects($this->at(1))
            ->method('publish')
            ->with(
                $file,
                'imaisu',
                $this->provider,
                $this->linker
            );

        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FILE_AFTER_PUBLISH, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->publisher->publish($file);

        return $file;
    }

    /**
     * @test
     * @depends publishShouldPublish
     */
    public function unpublishShouldUnpublish(File $file)
    {
        $data = $file->getData();
        $data['publisher.version_url']['ankan'] = 'kvaak-kvaak';
        $this->assertArrayHasKey('publisher.version_url', $data);

        $this->assertTrue($this->publisher->isPublished($file));

        $this->fiop->expects($this->once())->method('update')->with($file);

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FILE_BEFORE_UNPUBLISH, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->adapter
            ->expects($this->at(0))
            ->method('unpublish')
            ->with(
                $file,
                'ankan',
                $this->provider,
                $this->linker
            );

        $this->adapter
            ->expects($this->at(1))
            ->method('unpublish')
            ->with(
                $file,
                'imaisu',
                $this->provider,
                $this->linker
            );


        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FILE_AFTER_UNPUBLISH, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->publisher->unpublish($file);

        $this->assertFalse($this->publisher->isPublished($file));

        $this->assertArrayNotHasKey('publisher.version_url', $data);
    }

    /**
     * @test
     */
    public function unpublishedFileIsNotUnpublishedAgain()
    {
        $file = File::create(array('profile' => 'default'));
        $this->adapter->expects($this->never())->method('unpublish');
        $this->publisher->unpublish($file);
    }

    /**
     * @test
     */
    public function publishedFileIsNotPublishedAgain()
    {
        $file = File::create(array('profile' => 'default', 'data' => array('publisher.published' => 1)));
        $this->adapter->expects($this->never())->method('publish');
        $this->publisher->publish($file);
    }
}
