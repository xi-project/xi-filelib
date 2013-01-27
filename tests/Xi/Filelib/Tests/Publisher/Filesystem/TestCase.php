<?php

namespace Xi\Filelib\Tests\Publisher\Filesystem;

use Xi\Filelib\File\File;

class TestCase extends \Xi\Filelib\Tests\TestCase
{
    protected $versionProvider;

    protected $linker;

    protected $profileObject;

    protected $storage;

    protected $version;

    protected $fileOperator;

    public $resourcePaths = array();

    public $linkPaths = array();

    protected $plinker;

    public function setUp()
    {
        parent::setUp();

        $this->resourcePaths = array(
            1 => ROOT_TESTS . '/data/publisher/private/1/1',
            2 => ROOT_TESTS . '/data/publisher/private/2/2/2',
            3 => ROOT_TESTS . '/data/publisher/private/3/3/3/3',
            4 => ROOT_TESTS . '/data/publisher/private/666/4',
            5 => ROOT_TESTS . '/data/publisher/private/1/5'
        );

        $this->linkPaths = array(
            1 => 'lussin/tussin',
            2 => 'lussin/tussin/jussin/pussin',
            3 => 'tohtori/vesalan/suuri/otsa',
            4 => 'lussen/hof',
            5 => '',
        );

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker
            ->expects($this->any())
            ->method('getLinkVersion')
            ->will(
                $this->returnCallback(
                    function ($file, $version) {
                        return 'tussin/lussun/tussi-' . $version->getIdentifier() . '.jpg';
                    }
                )
            );

        $profileObject = $this->getMockFileProfile();

        $profileObject
            ->expects($this->any())
            ->method('getLinker')
            ->will($this->returnValue($linker));

        $versionProvider = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\VersionProvider')
            ->getMock();

        $versionProvider
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('xooxer'));

        $this->linker = $linker;
        $this->profileObject = $profileObject;
        $this->versionProvider = $versionProvider;

        $storage = $this
            ->getMockBuilder('Xi\Filelib\Storage\FilesystemStorage')
            ->getMock();

        $storage
            ->expects($this->any())
            ->method('getRoot')
            ->will($this->returnValue(ROOT_TESTS . '/data/publisher/private'));

        $storage
            ->expects($this->any())
            ->method('getDirectoryId')
            ->will(
                $this->returnCallback(
                    function ($file) {

                        switch ($file->getId()) {
                            case 1:
                                return '1';
                            case 2:
                                return '2/2';
                            case 3:
                                return '3/3/3';
                            case 4:
                                return '666';
                            case 5:
                                return '1';
                        }
                    }
                )
            );

        $this->storage = $storage;

        $plinker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();

        $self = $this;

        $plinker
            ->expects($this->any())
            ->method('getLinkVersion')
            ->will(
                $this->returnCallback(
                    function ($file, $version) use ($self) {
                        return $self->linkPaths[$file->getId()] . '/' . $file->getId() . '-' . $version . '.lus';
                    }
                )
            );

        $plinker
            ->expects($this->any())
            ->method('getLink')
            ->will(
                $this->returnCallback(
                    function ($file) use ($self) {
                        return $self->linkPaths[$file->getId()] . '/' . $file->getId() . '.lus';
                    }
                )
            );

        $this->plinker = $plinker;

        $this->fileOperator = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown()
    {
        $root = ROOT_TESTS . '/data/publisher/public';

        $diter = new \RecursiveDirectoryIterator($root);
        $riter = new \RecursiveIteratorIterator($diter, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($riter as $lus) {
            if (!in_array($lus->getFilename(), array('.', '..', '.gitignore'))) {
                if (!$lus->isDir() || $lus->isLink()) {
                    unlink($lus->getPathname());
                }
            }
        }

        foreach ($riter as $lus) {
            if (!in_array($lus->getFilename(), array('.', '..', '.gitignore'))) {
                if ($lus->isDir()) {
                    rmdir($lus->getPathname());
                }
            }
        }
    }
}
