<?php

namespace Xi\Filelib\Tests\Publisher;

use Xi\Filelib\Event\FileCopyEvent;
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

        $this->fiop = $this->getMockedFileRepository();

        $this->pm = $this->getMockedProfileManager();

        $this->pm
            ->expects($this->any())
            ->method('getProfile')
            ->with('default')
            ->will($this->returnValue($this->profile));

        $this->provider = $this->getMockedVersionProvider('lipsautus');

        $this->pm
            ->expects($this->any())
            ->method('getVersionProvider')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'), $this->logicalOr('ankan', 'imaisu'))
            ->will($this->returnValue($this->provider));

        $this->ed = $this->getMockedEventDispatcher();

        $filelib = $this->getMockedFilelib(null, $this->fiop, null, null, $this->ed, null, null, null, $this->pm);


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
            CoreEvents::FILE_BEFORE_DELETE => array('onBeforeDelete'),
            CoreEvents::FILE_BEFORE_COPY => array('onBeforeCopy')
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
    public function onBeforeCopyShouldResetTargetData()
    {
        $source = File::create();
        $sourceData = $source->getData();
        $sourceData['publisher.published'] = 1;
        $sourceData['publisher.version_url']['ankan'] = 'arto';
        $sourceData['publisher.version_url']['lipaisija'] = 'tenhunen';

        $target = clone $source;
        $targetData = $target->getData();

        $this->assertNotSame($sourceData, $targetData);

        $this->assertArrayHasKey('publisher.published', $sourceData);
        $this->assertArrayHasKey('publisher.published', $targetData);
        $this->assertArrayHasKey('publisher.version_url', $sourceData);
        $this->assertArrayHasKey('publisher.version_url', $targetData);

        $publisher = new Publisher($this->getMockedPublisherAdapter(), $this->getMockedLinker());

        $event = new FileCopyEvent($source, $target);
        $publisher->onBeforeCopy($event);

        $this->assertArrayHasKey('publisher.published', $sourceData);
        $this->assertArrayNotHasKey('publisher.published', $targetData);
        $this->assertArrayNotHasKey('publisher.version_url', $targetData);
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

        $ret = $this->publisher->getUrlVersion($file, 'ankan');
        $this->assertEquals('lussutusbansku', $ret);
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
            ->method('getUrlVersion')
            ->with(
                $file,
                'ankan',
                $this->provider,
                $this->linker
            )
            ->will($this->returnValue('tenhusen-suuruuden-ylistyksen-url'));

        $this->adapter
            ->expects($this->at(2))
            ->method('publish')
            ->with(
                $file,
                'imaisu',
                $this->provider,
                $this->linker
            );

        $this->adapter
            ->expects($this->at(3))
            ->method('getUrlVersion')
            ->with(
                $file,
                'imaisu',
                $this->provider,
                $this->linker
            )
            ->will($this->returnValue('tenhusen-ylistyksen-suuruuden-url'));

        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FILE_AFTER_PUBLISH, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $this->publisher->publish($file);

        $data = $file->getData();
        $this->assertEquals('tenhusen-suuruuden-ylistyksen-url', $data['publisher.version_url']['ankan']);
        $this->assertEquals('tenhusen-ylistyksen-suuruuden-url', $data['publisher.version_url']['imaisu']);

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
