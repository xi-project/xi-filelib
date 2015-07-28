<?php

namespace Xi\Filelib\Tests\Publisher;

use Xi\Filelib\Event\FileCopyEvent;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\RuntimeException;
use Xi\Filelib\Versionable\Version;
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

        $this->provider = $this->getMockedVersionProvider();

        $this->pm
            ->expects($this->any())
            ->method('getVersionProvider')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'), $this->logicalOr(Version::get('ankan'), Version::get('imaisu')))
            ->will($this->returnValue($this->provider));

        $this->ed = $this->getMockedEventDispatcher();

        $filelib = $this->getMockedFilelib(null, $this->fiop, null, null, $this->ed, null, null, null, $this->pm);


        $this->adapter = $this->getMockedPublisherAdapter();
        $this->linker = $this->getMockedLinker();

        $this->publisher = new Publisher($this->adapter, $this->linker);
        $this->publisher->attachTo($filelib);
    }

    /**
     * @test
     */
    public function adapterResolves()
    {
        $this->assertInstanceOf('Xi\Filelib\Tool\LazyReferenceResolver', $this->publisher->getAdapter());
        $this->assertSame($this->adapter, $this->publisher->getAdapter()->resolve());
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
            ->setMethods(array('unpublishAllVersions', 'publishAllVersions'))
            ->disableOriginalConstructor()
            ->getMock();

        $file = File::create();

        $publisher->expects($this->once())->method('unpublishAllVersions')->with($file);

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

        $sourceData->set(
            'publisher.version_url',
            array(
                'ankan' => 'arto',
                'lipaisija' => 'tenhunen',
            )
        );


        $target = clone $source;
        $targetData = $target->getData();

        $this->assertNotSame($sourceData, $targetData);

        $this->assertTrue($sourceData->has('publisher.version_url'));
        $this->assertTrue($targetData->has('publisher.version_url'));

        $publisher = new Publisher($this->getMockedPublisherAdapter(), $this->getMockedLinker());

        $event = new FileCopyEvent($source, $target);
        $publisher->onBeforeCopy($event);

        $this->assertTrue($sourceData->has('publisher.version_url'));
        $this->assertFalse($targetData->has('publisher.version_url'));
    }

    /**
     * @test
     */
    public function getNumberOfPublishedVersionsShouldDigToFileData()
    {
        $file = File::create();
        $this->assertEquals(0, $this->publisher->getNumberOfPublishedVersions($file));

        $data = $file->getData();

        $data->set('publisher.version_url', array(
            'lusso' => 'grande'
        ));

        $this->assertEquals(1, $this->publisher->getNumberOfPublishedVersions($file));

        $data->set('publisher.version_url', array(
            'lusso' => 'grande',
            'tenhusen' => 'suuruus',
        ));

        $this->assertEquals(2, $this->publisher->getNumberOfPublishedVersions($file));
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
    public function provideVersions()
    {
        return array(
            array('ankan'),
            array(Version::get('ankan'))
        );
    }

    /**
     * @test
     */
    public function isVersionPublishedReturnsCorrectResults()
    {
        $file = File::create();
        $this->assertFalse($this->publisher->isVersionPublished($file, 'tooxer'));

        $data = $file->getData();
        $data->set(
            'publisher.version_url',
            [
                'tooxer' => 'toooooooxers'
            ]
        );

        $this->assertTrue($this->publisher->isVersionPublished($file, 'tooxer'));
    }

    /**
     * @test
     */
    public function getPublishedVersionsReturnsPublishedVersions()
    {
        $file = File::create();
        $this->assertEquals([], $this->publisher->getPublishedVersions($file));

        $file->getData()->set(
            'publisher.version_url',
            [
                'sooser' => 'tenhunen',
                'turvaamistoimi' => 'toimii-aina'
            ]
        );

        $this->assertEquals(
            [
                'sooser',
                'turvaamistoimi'
            ],
            $this->publisher->getPublishedVersions($file)
        );
    }

    /**
     * @test
     * @dataProvider provideVersions
     */
    public function getUrlShouldDelegateToAdapterIfNoCachedData($version)
    {
        $file = File::create();

        $this->adapter
            ->expects($this->once())
            ->method('getUrl')
            ->with(
                $file,
                Version::get('ankan'),
                $this->provider,
                $this->linker
            )
            ->will($this->returnValue('lussutusbansku'));

        $ret = $this->publisher->getUrl($file, $version);
        $this->assertEquals('lussutusbansku', $ret);
    }

    /**
     * @test
     * @dataProvider provideVersions
     */
    public function getUrlShouldUseCachedDataWhenAvailable($version)
    {
        $file = File::create();
        $data = $file->getData();
        $data->set(
            'publisher.version_url',
            array(
                'ankan' => 'kerran-tenhusen-lipaisema-lopullisesti-pilalla'
            )
        );

        $this->adapter
            ->expects($this->never())
            ->method('getUrl');

        $this->fiop
            ->expects($this->never())
            ->method('update');

        $ret = $this->publisher->getUrl($file, $version);
        $this->assertEquals('kerran-tenhusen-lipaisema-lopullisesti-pilalla', $ret);
    }


    /**
     * @test
     */
    public function publishShouldPublish()
    {
        $version1 = Version::get('ankan');
        $version2 = Version::get('imaisu');

        $file = File::create(
            array(
                'profile' => 'default',
                'data' => array(
                    'versions' => array('ankan', 'imaisu')
                )
            )
        );

        $this->fiop->expects($this->once())->method('update')->with($file);

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FILE_BEFORE_PUBLISH, $this->isInstanceOf('Xi\Filelib\Event\PublisherEvent'));

        $this->adapter
            ->expects($this->at(1))
            ->method('publish')
            ->with(
                $file,
                $version1,
                $this->provider,
                $this->linker
            );

        $this->adapter
            ->expects($this->at(2))
            ->method('getUrl')
            ->with(
                $file,
                $version1,
                $this->provider,
                $this->linker
            )
            ->will($this->returnValue('tenhusen-suuruuden-ylistyksen-url'));

        $this->adapter
            ->expects($this->at(3))
            ->method('publish')
            ->with(
                $file,
                $version2,
                $this->provider,
                $this->linker
            );

        $this->adapter
            ->expects($this->at(4))
            ->method('getUrl')
            ->with(
                $file,
                $version2,
                $this->provider,
                $this->linker
            )
            ->will($this->returnValue('tenhusen-ylistyksen-suuruuden-url'));

        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FILE_AFTER_PUBLISH, $this->isInstanceOf('Xi\Filelib\Event\PublisherEvent'));

        $this->publisher->publishAllVersions($file);

        $versionUrls = $file->getData()->get('publisher.version_url');
        $this->assertEquals('tenhusen-suuruuden-ylistyksen-url', $versionUrls['ankan']);
        $this->assertEquals('tenhusen-ylistyksen-suuruuden-url', $versionUrls['imaisu']);

        return $file;
    }

    /**
     * @test
     * @depends publishShouldPublish
     */
    public function unpublishShouldUnpublish(File $file)
    {
        $data = $file->getData();

        $data->set(
            'publisher.version_url',
            array(
                'ankan' => 'kvaak-kvaak',
                'imaisu' => 'laarilaa'
            )
        );

        $version1 = Version::get('ankan');
        $version2 = Version::get('imaisu');

        $this->assertTrue($data->has('publisher.version_url'));

        $this->assertEquals(2, $this->publisher->getNumberOfPublishedVersions($file));

        $this->fiop->expects($this->once())->method('update')->with($file);

        $this->ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FILE_BEFORE_UNPUBLISH, $this->isInstanceOf('Xi\Filelib\Event\PublisherEvent'));

        $this->adapter
            ->expects($this->at(1))
            ->method('unpublish')
            ->with(
                $file,
                $version1,
                $this->provider,
                $this->linker
            );

        $this->adapter
            ->expects($this->at(2))
            ->method('unpublish')
            ->with(
                $file,
                $version2,
                $this->provider,
                $this->linker
            );


        $this->ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FILE_AFTER_UNPUBLISH, $this->isInstanceOf('Xi\Filelib\Event\PublisherEvent'));

        $this->publisher->unpublishAllVersions($file);

        $this->assertEquals(array(), $data->get('publisher.version_url'));
        $this->assertEquals(0, $this->publisher->getNumberOfPublishedVersions($file));
    }

    /**
     * @test
     */
    public function reverseUrlDelegatesToLinker()
    {
        $linker = $this->getMockedReversibleLinker();

        $linker
            ->expects($this->once())
            ->method('reverseLink')
            ->with('lussogrande-loso.lus')
            ->will($this->returnValue(array(File::create(), 'loso')));

        $publisher = new Publisher($this->adapter, $linker);

        list ($file, $version) = $publisher->reverseUrl('lussogrande-loso.lus');

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);
        $this->assertEquals('loso', $version);

    }

    /**
     * @test
     */
    public function reverseThrowsUpWithLegacyLinker()
    {
        $this->setExpectedException('Xi\Filelib\RuntimeException');

        $linker = $this->getMockedLinker();
        $publisher = new Publisher($this->adapter, $linker);

        $publisher->reverseUrl('lussogrande-loso.lus');
    }

    /**
     * @test
     */
    public function publishesVersion()
    {
        $file = File::create();
        $version = Version::get('ankan');

        $this->adapter
            ->expects($this->at(1))
            ->method('publish')
            ->with(
                $file,
                $version,
                $this->provider,
                $this->linker
            );

        $this->fiop
            ->expects($this->once())
            ->method('update')
            ->with(
                $file
            );

        $this->ed
            ->expects($this->exactly(2))
            ->method('dispatch');

        $ret = $this->publisher->publishVersion($file, $version);
        $this->assertTrue($ret);
    }

    /**
     * @test
     */
    public function doesntPublishVersionWhenAdapterThrowsUp()
    {
        $file = File::create();
        $version = Version::get('ankan');

        $this->adapter
            ->expects($this->at(1))
            ->method('publish')
            ->with(
                $file,
                $version,
                $this->provider,
                $this->linker
            )
            ->will($this->throwException(new RuntimeException()));

        $this->fiop
            ->expects($this->never())
            ->method('update');

        $this->ed
            ->expects($this->exactly(1))
            ->method('dispatch');

        $ret = $this->publisher->publishVersion($file, $version);
        $this->assertFalse($ret);
    }

    /**
     * @test
     */
    public function unpublishesVersion()
    {
        $file = File::create();
        $version = Version::get('ankan');

        $this->adapter
            ->expects($this->at(1))
            ->method('unpublish')
            ->with(
                $file,
                $version,
                $this->provider,
                $this->linker
            );

        $this->fiop
            ->expects($this->once())
            ->method('update')
            ->with(
                $file
            );

        $this->ed
            ->expects($this->exactly(2))
            ->method('dispatch');

        $ret = $this->publisher->unpublishVersion($file, $version);
        $this->assertTrue($ret);
    }

    /**
     * @test
     */
    public function doesntUnpublishVersionWhenAdapterThrowsUp()
    {
        $file = File::create();
        $version = Version::get('ankan');

        $this->adapter
            ->expects($this->at(1))
            ->method('unpublish')
            ->with(
                $file,
                $version,
                $this->provider,
                $this->linker
            )
            ->will($this->throwException(new RuntimeException()));

        $this->fiop
            ->expects($this->never())
            ->method('update');

        $this->ed
            ->expects($this->exactly(1))
            ->method('dispatch');

        $ret = $this->publisher->unpublishVersion($file, $version);
        $this->assertFalse($ret);
    }
}
