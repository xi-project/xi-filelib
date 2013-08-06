<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\File\FileProfile;
use Xi\Filelib\FileLibrary;

class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFilelib($methods = null, $fiop = null, $foop = null, $storage = null, $ed = null)
    {
        $filelib = $this
            ->getMockBuilder('Xi\Filelib\FileLibrary')
            ->disableOriginalConstructor();


        if ($methods !== null) {
            if ($fiop) {
                $methods[] = 'getFileOperator';
            }

            if ($foop) {
                $methods[] = 'getFolderOperator';
            }
            $filelib->setMethods(array_unique($methods));
        }

        $ret = $filelib->getMock();

        if ($fiop) {
            $ret->expects($this->any())->method('getFileOperator')->will($this->returnValue($fiop));
        }

        if ($foop) {
            $ret->expects($this->any())->method('getFolderOperator')->will($this->returnValue($foop));
        }

        if ($storage) {
            $ret->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        }

        if ($ed) {
            $ret->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($ed));
        }

        return $ret;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFileOperator($profileNames = array())
    {
        $profiles = array();
        foreach ($profileNames as $profileName) {
            $profiles[$profileName] = new FileProfile($profileName);
        }

        $fileop = $this
            ->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->disableOriginalConstructor()
            ->getMock();

        if ($profiles) {
            $fileop->expects($this->any())->method('getProfiles')->will($this->returnValue($profiles));

            $fileop
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

        return $fileop;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFolderOperator()
    {
        $folderop = $this
            ->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
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
    public function getMockedQueue()
    {
        return $this->getMock('Xi\Filelib\Queue\Queue');
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
    public function getMockedPlatform()
    {
        return $this->getMock('Xi\Filelib\Backend\Platform\Platform');
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
            ->getMockBuilder('Xi\Filelib\File\FileProfile')
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
    public function getMockedCommand()
    {
        return $this->getMock('Xi\Filelib\Command');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedEnqueueableCommand()
    {
        return $this->getMock('Xi\Filelib\EnqueueableCommand');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFolder()
    {
        return $this->getMock('Xi\Filelib\Folder\Folder');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedFile()
    {
        return $this->getMock('Xi\Filelib\File\File');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockedResource()
    {
        return $this->getMock('Xi\Filelib\File\Resource');
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
    public function getMockedVersionProvider($identifier)
    {
        $versionProvider = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\VersionProvider')
            ->getMock();

        $versionProvider
            ->expects($this->any())->method('getIdentifier')
            ->will($this->returnValue($identifier));

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
