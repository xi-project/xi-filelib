<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\VersionProvider;

use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Events;

/**
 * @group plugin
 */
class AbstractVersionProviderTest extends TestCase
{
    /**
     * @var ProfileManager
     */
    private $pm;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var AbstractVersionProvider
     */
    private $plugin;

    /**
     * @var Publisher
     */
    private $publisher;

    private $filelib;

    public function setUp()
    {
        parent::setUp();

        $this->storage = $this->getMockedStorage();

        $this->pm = $this->getMockedProfileManager(array('tussi', 'lussi'));

        $this->plugin = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider')
            ->setConstructorArgs(
                array(
                    function (File $file) {
                        return (bool) preg_match("/^(image|video)/", $file->getMimetype());
                    }
                )
            )
            ->getMockForAbstractClass();


        $filelib = $this->getMockedFilelib(null, null, null, $this->storage, null, null, null, null, $this->pm);

        $this->filelib = $filelib;

    }

    /**
     * @return array
     */
    public function provideVersions()
    {
        return array(
            array(array('tussi'), array('tussi'), true, true),
            array(array('tussi'), array('tussi'), false, false),
        );
    }

    /**
     * @dataProvider provideVersions
     * @test
     */
    public function areProvidedVersionsCreatedShouldReturnExpectedResults($resourceVersions, $pluginVersions, $sharedVersionsAllowed, $expectResourceGetVersions)
    {
        $this->plugin->expects($this->any())->method('areSharedVersionsAllowed')
             ->will($this->returnValue($sharedVersionsAllowed));

        $resource = $this->getMockedResource();

        $file = $this->getMockedFile();
        $file->expects($this->any())->method('getResource')->will($this->returnValue($resource));

        $this->plugin->expects($this->atLeastOnce())->method('areSharedVersionsAllowed')
            ->will($this->returnValue(false));

        $this->plugin->expects($this->any())->method('getProvidedVersions')->will($this->returnValue($pluginVersions));

        if ($expectResourceGetVersions) {
            $resource->expects($this->atLeastOnce())
                ->method('hasVersion')
                ->with($this->isType('string'))
                ->will($this->returnValue(true));
        } else {
            $file->expects($this->atLeastOnce())
                ->method('hasVersion')
                ->with($this->isType('string'))
                ->will($this->returnValue(true));
        }

        $this->assertEquals(true, $this->plugin->areProvidedVersionsCreated($file));

    }

    public function provideFilesForProvidesForMatching()
    {
        return array(
            array(true, array('profile' => 'tussi', 'mimetype' => 'image/png')),
            array(false, array('profile' => 'tussi', 'mimetype' => 'document/lus')),
            array(false, array('profile' => 'xtussi', 'mimetype' => 'image/xoo')),
            array(true, array('profile' => 'lussi', 'mimetype' => 'video/vii')),
            array(false, array('profile' => 'lussi', 'mimetype' => 'iimage/xoo')),
        );
    }

    /**
     * @test
     * @dataProvider provideFilesForProvidesForMatching
     */
    public function isApplicableToShouldMatchAgainstFileProfileCorrectly($expected, $file)
    {
        $this->plugin->attachTo($this->filelib);

        $file = $file + array(
            'resource' => Resource::create($file),
        );

        $file = File::create($file);

        $this->plugin->setProfiles(array('tussi', 'lussi'));


        $this->assertEquals($expected, $this->plugin->isApplicableTo($file));
    }

    /**
     * @test
     */
    public function afterUploadShouldDoNothingWhenPluginDoesNotProvide()
    {
        $this->plugin->attachTo($this->filelib);
        $this->plugin->expects($this->never())->method('createProvidedVersions');

        $this->plugin->setProfiles(array('tussi', 'lussi'));

        $file = File::create(array(
            'profile' => 'tussi',
            'resource' => Resource::create(array('mimetype' => 'iimage/xoo'))
        ));
        $event = new FileEvent($file);

        $this->plugin->onAfterUpload($event);
    }

    /**
     * @test
     */
    public function afterUploadShouldDoNothingWhenVersionAlreadyExists()
    {
        $this->plugin->attachTo($this->filelib);

        $this->plugin->expects($this->any())->method('areSharedVersionsAllowed')
            ->will($this->returnValue(true));

        $this->plugin->expects($this->never())->method('createProvidedVersions');

        $this->plugin->expects($this->atLeastOnce())->method('getProvidedVersions')
                     ->will($this->returnValue(array('reiska')));

        $this->plugin->setProfiles(array('tussi', 'lussi'));

        $file = File::create(
                    array(
                        'resource' => Resource::create(array('mimetype' => 'image/xoo', 'versions' => array('reiska'))),
                        'profile' => 'tussi',
                    )
                );
        $event = new FileEvent($file);

        $this->plugin->onAfterUpload($event);

    }

    public function provideSharedVersionsAllowed()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @test
     * @dataProvider provideSharedVersionsAllowed
     */
    public function afterUploadShouldCreateAndStoreVersionsWhenAllIsProper($sharedVersionsAllowed)
    {
        $this->plugin->attachTo($this->filelib);

        $this->plugin->expects($this->any())->method('areSharedVersionsAllowed')
            ->will($this->returnValue($sharedVersionsAllowed));

        $pluginVersions = array('xooxer', 'losobees');
        $this->plugin->expects($this->any())->method('getProvidedVersions')->will($this->returnValue($pluginVersions));

        $this->plugin->expects($this->once())->method('createTemporaryVersions')
             ->with($this->isInstanceOf('Xi\Filelib\File\File'))
             ->will($this->returnValue(array('xooxer' => ROOT_TESTS . '/data/temp/life-is-my-enemy-xooxer.jpg', 'losobees' => ROOT_TESTS . '/data/temp/life-is-my-enemy-losobees.jpg')));

        if ($sharedVersionsAllowed) {
            $this->plugin->expects($this->atLeastOnce())->method('getProvidedVersions')
                ->will($this->returnValue(array('reiska')));
        }

        $this->plugin->setProfiles(array('tussi', 'lussi'));

        $this->storage->expects($this->exactly(2))->method('storeVersion')
                ->with(
                    $sharedVersionsAllowed ? $this->isInstanceOf('Xi\Filelib\Resource\Resource') : $this->isInstanceOf('Xi\Filelib\File\File'),
                    $this->isType('string'),
                    $this->isType('string')
                );

        $file = File::create(array('profile' => 'tussi', 'resource' => Resource::create(array('mimetype' => 'image/xoo'))));
        $event = new FileEvent($file);

        $this->createMockedTemporaryFiles();

        $this->plugin->onAfterUpload($event);

        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/life-is-my-enemy-xooxer.jpg');
        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/life-is-my-enemy-losobees.jpg');
    }

    /**
     * @test
     */
    public function afterUploadShouldExitEarlyWhenPluginDoesntHaveProfile()
    {
        $this->plugin->attachTo($this->filelib);

        $this->plugin->expects($this->never())->method('createProvidedVersions');

        $this->plugin->setProfiles(array('tussi', 'lussi'));

        $storage = $this->storage;

        $storage->expects($this->never())->method('storeVersion');

        $file = File::create(array(
            'profile' => 'xooxer',
            'resource' => Resource::create(array('mimetype' => 'image/xoo'))
        ));
        $event = new FileEvent($file);

        $this->plugin->onAfterUpload($event);
    }

    /**
     * @test
     */
    public function onFileDeleteDelegates()
    {
        $plugin = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('deleteProvidedVersions'))
            ->getMockForAbstractClass();

        $file = File::create();
        $event = new FileEvent($file);
        $plugin->expects($this->once())->method('deleteProvidedVersions')->with($file);
        $plugin->onFileDelete($event);
    }

    /**
     * @test
     */
    public function onResourceDeleteDelegates()
    {
        $plugin = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('deleteProvidedVersions'))
            ->getMockForAbstractClass();

        $resource = Resource::create();
        $event = new ResourceEvent($resource);
        $plugin->expects($this->once())->method('deleteProvidedVersions')->with($resource);
        $plugin->onResourceDelete($event);
    }


    /**
     * @test
     */
    public function deleteProvidedVersionsIterates()
    {
        $this->plugin->attachTo($this->filelib);

        $this->storage->expects($this->once())->method('deleteVersion')
             ->with(
                     $this->isInstanceOf('Xi\Filelib\Resource\Resource'),
                     $this->equalTo('lusser')
              );

        $this->storage->expects($this->exactly(2))->method('versionExists')
            ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'), $this->isType('string'))
            ->will($this->onConsecutiveCalls(false, true));

        $this->plugin->setProfiles(array('tussi', 'lussi'));
        $this->plugin->expects($this->atLeastOnce())->method('getProvidedVersions')
                     ->will($this->returnValue(array('xooxer', 'lusser')));

        $resource = Resource::create(array('mimetype' => 'image/png'));
        $this->plugin->deleteProvidedVersions($resource);
    }

    private function createMockedTemporaryFiles()
    {
        copy(ROOT_TESTS . '/data/self-lussing-manatee.jpg', ROOT_TESTS . '/data/temp/life-is-my-enemy-xooxer.jpg');
        copy(ROOT_TESTS . '/data/self-lussing-manatee.jpg', ROOT_TESTS . '/data/temp/life-is-my-enemy-losobees.jpg');
        $this->assertFileExists(ROOT_TESTS . '/data/temp/life-is-my-enemy-xooxer.jpg');
        $this->assertFileExists(ROOT_TESTS . '/data/temp/life-is-my-enemy-losobees.jpg');
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = AbstractVersionProvider::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PROFILE_AFTER_ADD, $events);
        $this->assertArrayHasKey(Events::FILE_AFTER_AFTERUPLOAD, $events);
        $this->assertArrayHasKey(Events::FILE_AFTER_DELETE, $events);
        $this->assertArrayHasKey(Events::RESOURCE_AFTER_DELETE, $events);
    }

    /**
     * @xxxtest
     */
    public function deleteVersionShouldDelegateToStorage()
    {
        $this->plugin->attachTo($this->filelib);

        $file = File::create(array('id' => 666, 'resource' => Resource::create()));

        $this->storage
             ->expects($this->once())
             ->method('deleteVersion')
             ->with($this->isInstanceOf('Xi\Filelib\File\File'), $this->equalTo('xooxersson'));

        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider')
            ->setConstructorArgs(array(
                $this->storage,
                $this->publisher,
                $this->pm
            ))
            ->getMockForAbstractClass();

        $plugin->setIdentifier('xooxersson');
        $plugin->expects($this->atLeastOnce())->method('getProvidedVersions')
               ->will($this->returnValue(array('xooxersson')));

        $plugin->deleteVersions($file);
    }

    /**
     * @test
     */
    public function onDeleteResourceShouldDelegateToStorage()
    {
        $this->plugin->attachTo($this->filelib);

        $this->plugin->expects($this->atLeastOnce())->method('getProvidedVersions')
             ->will($this->returnValue(array('xooxer', 'lusser')));

        $this->storage->expects($this->exactly(2))->method('versionExists')
             ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'),
                    $this->isType('string'))
             ->will($this->onConsecutiveCalls(true, false));

        $this->storage->expects($this->once())
             ->method('deleteVersion')
             ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'), $this->isType('string'));

        $resource = Resource::create(array('mimetype' => 'image/png'));
        $event = new ResourceEvent($resource);

        $this->plugin->onResourceDelete($event);
    }

    public function provideFiles()
    {
        return array(
            array('image/jpeg', ROOT_TESTS . '/data/self-lussing-manatee.jpeg'),
            array('image/png', ROOT_TESTS . '/data/dporssi-screenshot.png'),
        );
    }

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function getMimeTypeReturnsMimeType($expected, $filename)
    {
        $this->plugin->attachTo($this->filelib);

        $resource = Resource::create();
        $file = File::create(array('resource' => $resource));

        $this->plugin
            ->expects($this->atLeastOnce())
            ->method('areSharedVersionsAllowed')
            ->will($this->returnValue(true));

        $this->storage
            ->expects($this->once())
            ->method('retrieveVersion')
            ->with($resource, 'xoox')
            ->will($this->returnValue($filename));

        $mimeType = $this->plugin->getMimeType($file, 'xoox');
        $this->assertSame($expected, $mimeType);
    }


    /**
     * @test
     * @dataProvider provideFiles
     */
    public function getExtensionShouldQueryExtensionAndReplaceFugly($expected, $filename)
    {
        $file = File::create();

        $plugin = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider')
            ->setConstructorArgs(
                array(
                    'xooxer',
                    function (File $file) {
                        return (bool) preg_match("/^(image|video)/", $file->getMimetype());
                    }
                )
            )
            ->setMethods(array('getMimeType'))
            ->getMockForAbstractClass();

        $plugin
            ->expects($this->once())
            ->method('getMimeType')
            ->with($file, 'xooxer')
            ->will($this->returnValue('image/jpeg'));

        $extension = $plugin->getExtension($file, 'xooxer');
        $this->assertEquals('jpg', $extension);
    }

    /**
     * @test
     */
    public function isNotLazy()
    {
        $plugin = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider')
            ->setConstructorArgs(
                array(
                    'xooxer',
                    function (File $file) {
                        return (bool) preg_match("/^(image|video)/", $file->getMimetype());
                    }
                )
            )
            ->getMockForAbstractClass();

        $this->assertFalse($plugin->canBeLazy());

    }
}
