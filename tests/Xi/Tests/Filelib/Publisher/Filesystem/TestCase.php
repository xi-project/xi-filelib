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

                        case 4:
                            return '666';
                            
                        case 5:
                            return '1';
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

                        case 4:
                            return ROOT_TESTS . '/data/publisher/private/666/4';
                        case 5:
                            return ROOT_TESTS . '/data/publisher/private/1/5';
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

                        case 4:
                            return ROOT_TESTS . '/data/publisher/private/666/4';
                            
                        case 5:
                            return ROOT_TESTS . '/data/publisher/private/1/5';
                    }
                    
        }));

        
        $this->storage = $storage;
        
        $filelib = new FileLibrary();
        
        $this->filelib = $filelib;
        
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
