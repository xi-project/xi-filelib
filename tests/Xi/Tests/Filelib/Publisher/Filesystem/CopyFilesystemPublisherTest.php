<?php

namespace Xi\Tests\Filelib\Publisher\Filesystem;


use Xi\Filelib\File\FileItem;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Filesystem\CopyPublisher;

class CopyFilesystemPublisherTest extends TestCase
{
    
    
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
        mkdir(dirname($link), 0700, true);
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
