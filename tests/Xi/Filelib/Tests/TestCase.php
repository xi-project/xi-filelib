<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\FileLibrary;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function getMockAcl()
    {
        return $this->getMock('\Xi\Filelib\Acl\Acl');
    }

    public function getMockStorage()
    {
        return $this->getMockForAbstractClass('\Xi\Filelib\Storage\AbstractStorage');
    }

    public function getMockBackend()
    {
        return $this->getMockForAbstractClass('\Xi\Filelib\Backend\AbstractPlatform');
    }

    public function getMockPublisher()
    {
        return $this->getMockForAbstractClass('\Xi\Filelib\Publisher\AbstractPublisher');
    }

    public function getMockFileProfile()
    {
        return $this->getMockBuilder('Xi\Filelib\File\FileProfile')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getFileOperatorMock()
    {
        return $this->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function getFilelib()
    {
        $filelib = new FileLibrary();
        $filelib->setTempDir(ROOT_TESTS . '/data/temp');

        return $filelib;
    }
}
