<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Profile\FileProfile;
use Xi\Filelib\FileLibrary;

class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFilelib(
        $methods = null,
        $fire = null,
        $fore = null,
        $storage = null,
        $ed = null,
        $backend = null,
        $commander = null,
        $queue = null,
        $pm = null
    ) {

        // Beautiful backwards compatible kludge. My best code evah!
        $rere = null;
        $args = func_get_args();
        if (isset($args[1]) && is_array($args[1])) {
            $defaults = array(
                'fire' => null,
                'fore' => null,
                'rere' => null,
                'storage' => null,
                'ed' => null,
                'backend' => null,
                'commander' => null,
                'queue' => null,
                'pm' => null,
            );

            $mocks = array_merge($defaults, $args[1]);
            extract($mocks, EXTR_OVERWRITE);
        }

        $filelib = $this
            ->getMockBuilder('Xi\Filelib\FileLibrary')
            ->disableOriginalConstructor();


        if ($methods !== null) {
            if ($fire) {
                $methods[] = 'getFileRepository';
            }

            if ($fore) {
                $methods[] = 'getFolderRepository';
            }
            $filelib->setMethods(array_unique($methods));
        }

        $ret = $filelib->getMock();

        if ($fire) {
            $ret->expects($this->any())->method('getFileRepository')->will($this->returnValue($fire));
        }

        if ($fore) {
            $ret->expects($this->any())->method('getFolderRepository')->will($this->returnValue($fore));
        }

        if ($rere) {
            $ret->expects($this->any())->method('getResourceRepository')->will($this->returnValue($rere));
        }

        if ($storage) {
            $ret->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        }

        if ($ed) {
            $ret->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($ed));
        }

        if ($backend) {
            $ret->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        }

        if ($queue) {
            $ret->expects($this->any())->method('getQueue')->will($this->returnValue($queue));
        }

        if ($pm) {
            $ret->expects($this->any())->method('getProfileManager')->will($this->returnValue($pm));
        }

        if (!$commander) {
            $commander = $this->getMockedCommander();
        }
        $ret->expects($this->any())->method('getCommander')->will($this->returnValue($commander));

        return $ret;
    }

    public function getMockedProfileManager($profileNames = array())
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Profile\ProfileManager')->disableOriginalConstructor()->getMock();

        $profiles = array();
        foreach ($profileNames as $key => $profileName) {
            $profiles[$profileName] = new FileProfile($profileName);
        }

        if ($profiles) {
            $mock->expects($this->any())->method('getProfiles')->will($this->returnValue($profiles));
            $mock
                ->expects($this->any())
                ->method('getProfile')
                ->will(
                    $this->returnCallback(
                        function ($name) use ($profiles) {
                            return $profiles[$name];
                        }
                    )
                );
        }

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFileRepository($methods = array())
    {

        $fileop = $this
            ->getMockBuilder('Xi\Filelib\File\FileRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $fileop;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedResourceRepository($methods = array())
    {
        $repo = $this
            ->getMockBuilder('Xi\Filelib\Resource\ResourceRepository')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $repo;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFolderRepository()
    {
        $folderop = $this
            ->getMockBuilder('Xi\Filelib\Folder\FolderRepository')
            ->disableOriginalConstructor()
            ->getMock();

        return $folderop;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedStorage()
    {
        return $this->getMock('Xi\Filelib\Storage\Storage');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedLinker()
    {
        return $this->getMock('Xi\Filelib\Publisher\Linker');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedReversibleLinker()
    {
        return $this->getMock('Xi\Filelib\Publisher\ReversibleLinker');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedQueue()
    {
        return $this
            ->getMockBuilder('Pekkis\Queue\SymfonyBridge\EventDispatchingQueue')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedQueueAdapter()
    {
        return $this->getMock('Pekkis\Queue\Adapter\Adapter');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedEventDispatcher()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedPublisher()
    {
        return $this->getMockBuilder('Xi\Filelib\Publisher\Publisher')->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedPublisherAdapter()
    {
        return $this->getMock('Xi\Filelib\Publisher\PublisherAdapter');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedBackendAdapter()
    {
        return $this->getMock('Xi\Filelib\Backend\Adapter\BackendAdapter');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedCommander()
    {
        return $this->getMockBuilder('Xi\Filelib\Command\Commander')->disableOriginalConstructor()->getMock();
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedBackend()
    {
        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        return $backend;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFileProfile($name = null)
    {
        $profile = $this
            ->getMockBuilder('Xi\Filelib\Profile\FileProfile')
            ->disableOriginalConstructor()
            ->getMock();

        if ($name) {
            $profile
                ->expects($this->any())
                ->method('getIdentifier')
                ->will($this->returnValue($name));
        }

        return $profile;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedPlugin()
    {
        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        return $plugin;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedCommand($topic = 'some_random_topic', $expectToBeExecuted = null)
    {
        $mock = $this->getMock('Xi\Filelib\Command\Command');
        $mock->expects($this->any())->method('getTopic')->will($this->returnValue($topic));

        // Horrible fate :(
        if (!is_null($expectToBeExecuted)) {
            $mock->expects($this->once())->method('execute')->will($this->returnValue($expectToBeExecuted));
        }
        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedExecutable($expectToBeExecuted = null)
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Command\Executable')->disableOriginalConstructor()->getMock();

        // Another horrible fate :(
        if (!is_null($expectToBeExecuted)) {
            $mock->expects($this->once())->method('execute')->will($this->returnValue($expectToBeExecuted));
        }

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedExecutionStrategy()
    {
        return $this->getMock('Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFolder()
    {
        return $this
            ->getMockBuilder('Xi\Filelib\Folder\Folder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFile($profile = 'versioned')
    {
        $file = $this
            ->getMockBuilder('Xi\Filelib\File\File')
            ->disableOriginalConstructor()
            ->getMock();
        $file
            ->expects($this->any())
            ->method('getProfile')
            ->will($this->returnValue($profile));

        return $file;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedResource()
    {
        return $this->getMockBuilder('Xi\Filelib\Resource\Resource')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedCacheAdapter()
    {
        return $this->getMock('Xi\Filelib\Backend\Cache\Adapter\CacheAdapter');
    }

    public function assertClassExists($className)
    {
        $this->assertTrue(
            class_exists($className),
            "Class '{$className}' does not exist"
        );
    }

    public function assertInterfaceExists($interfaceName)
    {
        $this->assertTrue(
            interface_exists($interfaceName),
            "Interface '{$interfaceName}' does not exist"
        );
    }

    public function assertImplements($implemented, $implementor)
    {
        $this->assertContains(
            $implemented,
            class_implements($implementor),
            "Class '{$implementor}' doesnt implement '{$implemented}'"
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedVersionProvider($identifier, $versions = array())
    {
        $versionProvider = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\VersionProvider')
            ->getMock();

        $versionProvider
            ->expects($this->any())->method('getIdentifier')
            ->will($this->returnValue($identifier));

        $versionProvider
            ->expects($this->any())
            ->method('getProvidedVersions')
            ->will($this->returnValue($versions));

        return $versionProvider;
    }



    public function assertUuid($what)
    {
        $this->assertRegexp(
            '/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/',
            $what,
            "'{$what}' is not an UUID"
        );
    }


}
