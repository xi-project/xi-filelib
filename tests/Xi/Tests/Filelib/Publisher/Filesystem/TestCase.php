<?php

namespace Xi\Tests\Filelib\Publisher\Filesystem;

use Xi\Filelib\File\FileItem;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Filesystem\SymlinkPublisher;


class TestCase extends \Xi\Tests\Filelib\TestCase
{

    protected $versionProvider;
    protected $linker;
    protected $profileObject;
    protected $storage;
    protected $filelib;

    
    public function provideDataForPublishingTests()
    {
        $files = array();
        
        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        
        $linker->expects($this->any())->method('getLinkVersion')
                ->will($this->returnCallback(function($file, $version) { 
                    
                    switch ($file->getId()) {
                        
                        case 1:
                            $prefix = 'lussin/tussin';
                            break;
                        case 2:
                            $prefix = 'lussin/tussin/jussin/pussin';
                            break;
                        case 3:
                            $prefix = 'tohtori/vesalan/suuri/otsa';
                            break;
                        default:
                            $prefix = 'lussen/hof';
                        
                    }
                    
                    
                    return $prefix . '/' . $file->getId() . '-' . $version->getIdentifier() . '.lus';
                    
                }));
        $linker->expects($this->any())->method('getLink')
                ->will($this->returnCallback(function($file) {
                    
                    switch ($file->getId()) {
                        
                        case 1:
                            $prefix = 'lussin/tussin';
                            break;
                        case 2:
                            $prefix = 'lussin/tussin/jussin/pussin';
                            break;
                        case 3:
                            $prefix = 'tohtori/vesalan/suuri/otsa';
                            break;
                        default:
                            $prefix = 'lussen/hof';
                        
                    }
                    
                    return $prefix . '/' . $file->getId() . '.lus';
                 }));
        
        
        $profileObject = $this->getMockBuilder('Xi\Filelib\File\FileProfile')
                                ->getMock();
        $profileObject->expects($this->any())->method('getLinker')
                      ->will($this->returnCallback(function() use ($linker) { return $linker; }));
                                
        for ($x = 1; $x <= 4; $x++) {
            $file = $this->getMockBuilder('Xi\Filelib\File\FileItem')->getMock();
            $file->expects($this->any())->method('getProfileObject')
                    ->will($this->returnCallback(function() use ($profileObject) { return $profileObject; }));
            $file->expects($this->any())->method('getId')->will($this->returnValue($x));
            
            $files[$x-1] = $file;
        }
        
        $ret = array(
            array(
                $files[0],
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/1.lus',
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/1-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/1/1',
                '../../../private/1/1',
            ),
            array(
                $files[1],
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/jussin/pussin/2.lus',
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/jussin/pussin/2-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/2/2/2',
                '../../../../../private/2/2/2',
            ),
            array(
                $files[2],
                ROOT_TESTS . '/data/publisher/public/tohtori/vesalan/suuri/otsa/3.lus',
                ROOT_TESTS . '/data/publisher/public/tohtori/vesalan/suuri/otsa/3-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/3/3/3/3',
                '../../../../../private/3/3/3/3',
            ),
            array(
                $files[3],
                ROOT_TESTS . '/data/publisher/public/lussen/hof/4.lus',
                ROOT_TESTS . '/data/publisher/public/lussen/hof/4-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/666/4',
                '../../../private/666/4',
            ),
        );
        
        return $ret;
        
    }

    
    
    
    public function setUp()
    {
        parent::setUp();
        
        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker->expects($this->any())->method('getLinkVersion')
                ->will($this->returnCallback(function($file, $version) { return 'tussin/lussun/tussi-' . $version->getIdentifier() . '.jpg'; }));
        
        $profileObject = $this->getMockBuilder('Xi\Filelib\File\FileProfile')
                                ->getMock();
        $profileObject->expects($this->any())->method('getLinker')
                      ->will($this->returnCallback(function() use ($linker) { return $linker; }));
                  
        
        $versionProvider = $this->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\VersionProvider')->getMock();
        $versionProvider->expects($this->any())->method('getIdentifier')
        ->will($this->returnCallback(function() { return 'xooxer'; }));
        
        
        $this->linker = $linker;
        $this->profileObject = $profileObject;
        $this->versionProvider = $versionProvider;
        
        $storage = $this->getMockBuilder('Xi\Filelib\Storage\FilesystemStorage')->getMock();
        $storage->expects($this->any())->method('getRoot')
                ->will($this->returnValue(ROOT_TESTS . '/data/publisher/private'));
     
        $storage->expects($this->any())->method('getDirectoryId')
                ->will($this->returnCallback(function($file){
                 
                    switch ($file->getId()) {
                    
                        case 1:
                            return '1';
                            
                        case 2:
                            return '2/2';
                            
                        case 3:
                            return '3/3/3';

                        default:
                            return '666';
                    }
                        
                    
                    
        }));
        
        $storage->expects($this->any())->method('retrieve')
                ->will($this->returnCallback(function($file){
                 
                    switch ($file->getId()) {
                    
                        case 1:
                            return ROOT_TESTS . '/data/publisher/private/1/1';
                            
                        case 2:
                            return ROOT_TESTS . '/data/publisher/private/2/2/2';
                            
                        case 3:
                            return ROOT_TESTS . '/data/publisher/private/3/3/3/3';

                        default:
                            return ROOT_TESTS . '/data/publisher/private/666/4';
                    }
                    
        }));

        $storage->expects($this->any())->method('retrieveVersion')
                ->will($this->returnCallback(function($file, $version){
                 
                    switch ($file->getId()) {
                    
                        case 1:
                            return ROOT_TESTS . '/data/publisher/private/1/1';
                            
                        case 2:
                            return ROOT_TESTS . '/data/publisher/private/2/2/2';
                            
                        case 3:
                            return ROOT_TESTS . '/data/publisher/private/3/3/3/3';

                        default:
                            return ROOT_TESTS . '/data/publisher/private/666/4';
                    }
                    
        }));

        
        $this->storage = $storage;
        
        $filelib = new FileLibrary();
        
        $this->filelib = $filelib;
        
    }

    
    
    
    public function tearDown()
    {
        parent::tearDown();
                        
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
