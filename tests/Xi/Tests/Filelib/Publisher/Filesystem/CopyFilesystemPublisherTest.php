<?php

namespace Xi\Tests\Filelib\Publisher\Filesystem;


use Xi\Filelib\File\FileItem;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Filesystem\CopyPublisher;

class CopyFilesystemPublisherTest extends TestCase
{
    
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
                        case 4:
                            $prefix = 'lussen/hof';
                            break;
                        case 5:
                            $prefix = '';
                            break;
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
                        case 4:
                            $prefix = 'lussen/hof';
                            break;
                        case 5:
                            $prefix = '';
                            break;

                        
                    }
                    
                    return $prefix . '/' . $file->getId() . '.lus';
                 }));
        
        
        $profileObject = $this->getMockBuilder('Xi\Filelib\File\FileProfile')
                                ->getMock();
        $profileObject->expects($this->any())->method('getLinker')
                      ->will($this->returnCallback(function() use ($linker) { return $linker; }));
                                
        for ($x = 1; $x <= 5; $x++) {
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
            array(
                $files[4],
                ROOT_TESTS . '/data/publisher/public/5.lus',
                ROOT_TESTS . '/data/publisher/public/5-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/1/5',
                '../../../private/1/5',
            ),

            
        );
        
        return $ret;
        
    }

    
    
    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFile($file, $expectedPath, $expectedVersionPath, $expectedRealPath)
    {
        $this->filelib->setStorage($this->storage);
        
        $publisher = new CopyPublisher();
        $publisher->setFilelib($this->filelib);
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        // $publisher->setRelativePathToRoot('../private');
        
        $file->setFilelib($this->filelib);
                
        $publisher->publish($file);
        
        $sfi = new \SplFileInfo($expectedPath);
                        
        $this->assertFalse($sfi->isLink(), "File '{$expectedPath}' is a symbolic link");
        
        $this->assertTrue($sfi->isReadable(), "File '{$expectedPath}' is not a readable symbolic link");
        
        $this->assertFileEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");
        
    }
    

    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileVersion($file, $expectedPath, $expectedVersionPath, $expectedRealPath)
    {
        $this->filelib->setStorage($this->storage);
        
        $publisher = new CopyPublisher();
        $publisher->setFilelib($this->filelib);
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        // $publisher->setRelativePathToRoot('../private');
        
        $file->setFilelib($this->filelib);
                
        $publisher->publishVersion($file, $this->versionProvider);
        
        $sfi = new \SplFileInfo($expectedVersionPath);
                        
        $this->assertFalse($sfi->isLink(), "File '{$expectedVersionPath}' is a symbolic link");
        
        $this->assertTrue($sfi->isReadable(), "File '{$expectedVersionPath}' is not a readable symbolic link");
        
                
        $this->assertFileEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");
        
    }

    

    private function createFile($target, $link)
    {
        if (!is_dir(dirname($link))) {
            mkdir(dirname($link), 0700, true);
        }
        copy($target, $link);
    }
    
    
    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function unpublishShouldUnpublishFile($file, $expectedPath, $expectedVersionPath, $expectedRealPath, $expectedRelativePath)
    {
        $this->createFile($expectedRealPath, $expectedPath);
        
        $this->assertFileExists($expectedPath);
        
        $publisher = new CopyPublisher();
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        
        $publisher->unpublish($file);
        
        $this->assertFileNotExists($expectedPath);
        
    }
    
    
    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function unpublishVersionShouldUnpublishFileVersion($file, $expectedPath, $expectedVersionPath, $expectedRealPath, $expectedRelativePath)
    {
        $this->createFile($expectedRealPath, $expectedVersionPath);
        
        $this->assertFileExists($expectedVersionPath);
        
        $publisher = new CopyPublisher();
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        
        $publisher->unpublishVersion($file, $this->versionProvider);
        
        $this->assertFileNotExists($expectedVersionPath);
        
    }
    
    
    
    
    
    
}
