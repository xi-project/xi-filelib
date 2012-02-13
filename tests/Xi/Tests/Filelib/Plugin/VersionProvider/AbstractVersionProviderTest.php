<?php

namespace Xi\Tests\Filelib\Plugin\VersionProvider;

use Xi\Tests\Filelib\TestCase;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\File\FileItem;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\FileOperator;

class AbstractVersionProviderTest extends TestCase
{
    
    /**
     *
     * @var FileLibrary
     */
    protected $filelib;
    
    /**
     *
     * @var FileOperator
     */
    protected $fileOperator;
    
    /**
     *
     * @var Storage
     */
    protected $storage;
    
    /**
     *
     * @var AbstractVersionProvider
     */
    protected $plugin;
    
    /**
     * @var Publisher
     */
    protected $publisher;
    
    /**
     *
     * @return FileLibrary
     */
    public function setUp()
    {
        $fileOperator = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        
        $fileOperator->expects($this->any())->method('getType')
        ->will($this->returnCallback(function(FileItem $file) {
            $split = explode('/', $file->getMimetype());
            return $split[0];
        }));
                        
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnCallback(function() use ($fileOperator) { 
            return $fileOperator;
        }));

        $storage = $this->getMockForAbstractClass('Xi\Filelib\Storage\Storage');
        $filelib->expects($this->any())->method('getStorage')->will($this->returnCallback(function() use ($storage) { 
            return $storage;
        }));
        
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider')
            ->setMethods(array('createVersion', 'deleteVersion'))
            ->getMockForAbstractClass();

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Publisher')
            ->getMockForAbstractClass();
        $filelib->expects($this->any())->method('getPublisher')->will($this->returnCallback(function() use ($publisher) { 
            return $publisher;
        }));
                
        $this->plugin = $plugin;
        $this->filelib = $filelib;
        $this->fileOperator = $fileOperator;
        $this->storage = $storage;
        $this->publisher = $publisher;
        
    }
    
    
    public function tearDown()
    {
        parent::tearDown();
    }
    
    
    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $providesFor = array('image', 'video');
        $this->assertEquals(array(), $this->plugin->getProvidesFor());
        $this->assertSame($this->plugin, $this->plugin->setProvidesFor($providesFor));
        $this->assertEquals($providesFor, $this->plugin->getProvidesFor());
        
        $identifier = 'xooxer';
        $this->assertNull($this->plugin->getIdentifier());
        $this->assertSame($this->plugin, $this->plugin->setIdentifier($identifier));
        $this->assertEquals($identifier, $this->plugin->getIdentifier());
        
        $extension = 'lus';
        $this->assertNull($this->plugin->getExtension());
        $this->assertSame($this->plugin, $this->plugin->setExtension($extension));
        $this->assertEquals($extension, $this->plugin->getExtension());
        
    }
    
    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function initShouldFailWhenExtensionAndIdentifierAreNotDefined()
    {
        $this->plugin->init();
    }

    
    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function initShouldFailWhenExtensionIsNotDefined()
    {
        $this->plugin->setIdentifier('xooxer');
        
        $this->plugin->init();
        
        
    }

    
    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function initShouldFailWhenIdentifierIsNotDefined()
    {
        $this->plugin->setExtension('lus');
        
        $this->plugin->init();
        
    }

    /**
     * @test
     */
    public function initShouldPassWhenIdentifierAndExtensionAreSetAndNoProvidesAreSetToPlugin()
    {
        $this->plugin->setExtension('lus');
        $this->plugin->setIdentifier('xooxer');
        
        $this->plugin->init();
        
    }
    
    
    /**
     * @test
     */
    public function initShouldPassWhenIdentifierAndExtensionAreSetAndProvidesAreSetToPlugin()
    {
        $this->plugin->setExtension('lus');
        $this->plugin->setIdentifier('xooxer');
        
        $this->plugin->setProvidesFor(array('image', 'video'));
        
        
        $this->plugin->init();
        
    }
    
    
    /**
     * @test
     */
    public function initShouldRegisterToProfilesWhenIdentifierAndExtensionAreSetAndProvidesAndProfilesAreSetToPlugin()
    {
        $this->plugin->setExtension('lus');
        $this->plugin->setIdentifier('xooxer');
        
        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));
        
        $lussi = $this->getMock('Xi\Filelib\File\FileProfile');
        $lussi->expects($this->at(0))->method('addFileVersion')->with($this->equalTo('image'), $this->equalTo('xooxer'), $this->isInstanceOf('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider'));
        $lussi->expects($this->at(1))->method('addFileVersion')->with($this->equalTo('video'), $this->equalTo('xooxer'), $this->isInstanceOf('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider'));
        
        $tussi = $this->getMock('Xi\Filelib\File\FileProfile');
        $tussi->expects($this->at(0))->method('addFileVersion')->with($this->equalTo('image'), $this->equalTo('xooxer'), $this->isInstanceOf('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider'));
        $tussi->expects($this->at(1))->method('addFileVersion')->with($this->equalTo('video'), $this->equalTo('xooxer'), $this->isInstanceOf('Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider'));
                                
        $fileOperator = $this->fileOperator;
        
        $fileOperator->expects($this->any())->method('getProfile')
        ->with($this->logicalOr(
            $this->equalTo('tussi'), $this->equalTo('lussi')
        ))
        ->will($this->returnCallback(function($name) use ($lussi, $tussi) {
            
            if ($name === 'lussi') {
                return $lussi;
            }
            
            if ($name === 'tussi') {
                return $tussi;
            }
            
        }));
        
        
        $this->plugin->setFilelib($this->filelib);
        $this->plugin->init();
        
    }
    
    
    public function prodiveFilesForProvidesForMatching()
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
     * @dataProvider prodiveFilesForProvidesForMatching
     */
    public function providesForShouldMatchAgainstFileProfileCorrectly($expected, $file)
    {
        $file = FileItem::create($file);
        
        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));
        
        $this->plugin->setFilelib($this->filelib);
        
        $this->assertEquals($expected, $this->plugin->providesFor($file));
        
        
    }
    
    
    public function onDeleteShouldDelegateToPublisher()
    {
        
    }
    
    
    
    /**
     * @test
     */
    public function afterUploadShouldDoNothingWhenPluginDoesNotProvide()
    {
        $this->plugin->expects($this->never())->method('createVersion');
        
        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));

        
        $this->plugin->setFileLib($this->filelib);
               
                
        $file = FileItem::create(array('mimetype' => 'iimage/xoo', 'profile' => 'tussi'));
        $this->plugin->afterUpload($file);
        
        
    }
    
    
    /**
     * @test
     */
    public function afterUploadShouldCreateAndStoreVersionWhenPluginProvides()
    {
        
        $this->plugin->expects($this->once())->method('createVersion')->will($this->returnCallback(function() {
           return ROOT_TESTS . '/data/temp/life-is-my-enemy.jpg';
        }));
        
        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));

        $filelib = $this->filelib;
        $storage = $this->storage;
        
        $storage->expects($this->once())->method('storeVersion');
                
        $this->plugin->setFilelib($filelib);
        
        $file = FileItem::create(array('mimetype' => 'image/xoo', 'profile' => 'tussi'));
        
        
        $this->createMockedTemporaryFile();
        
        $this->plugin->afterUpload($file);
        
        $this->assertFileNotExists(ROOT_TESTS . '/data/temp/life-is-my-enemy.jpg');
        
        
    }
    
   
    /**
     * @test
     */
    public function onPublishShouldDoNothingWhenPluginDoesNotProvide()
    {
        $this->publisher->expects($this->never())->method('publishVersion');

        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));
        
        $this->plugin->setFileLib($this->filelib);
                
        $file = FileItem::create(array('mimetype' => 'iimage/xoo', 'profile' => 'tussi'));
        $this->plugin->onPublish($file);
        
    }
    
    
    /**
     * @test
     */
    public function onPublishShouldPublishWhenPluginProvides()
    {
        $this->publisher->expects($this->once())->method('publishVersion');

        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));
        
        $this->plugin->setFileLib($this->filelib);
                
        $file = FileItem::create(array('mimetype' => 'image/png', 'profile' => 'tussi'));
        $this->plugin->onPublish($file);
        
    }
    
    /**
     * @test
     */
    public function onUnpublishShouldDoNothingWhenPluginDoesNotProvide()
    {
        $this->publisher->expects($this->never())->method('unpublishVersion');

        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));
        
        $this->plugin->setFileLib($this->filelib);
                
        $file = FileItem::create(array('mimetype' => 'iimage/xoo', 'profile' => 'tussi'));
        $this->plugin->onUnpublish($file);
        
    }
    
    
    /**
     * @test
     */
    public function onUnpublishShouldUnpublishWhenPluginProvides()
    {
        $this->publisher->expects($this->once())->method('unpublishVersion');

        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));
        
        $this->plugin->setFileLib($this->filelib);
                
        $file = FileItem::create(array('mimetype' => 'image/png', 'profile' => 'tussi'));
        $this->plugin->onUnpublish($file);
        
    }
    

        /**
     * @test
     */
    public function onDeleteShouldDoNothingWhenPluginDoesNotProvide()
    {
        $this->storage->expects($this->never())->method('deleteVersion');

        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));
        
        $this->plugin->setFileLib($this->filelib);
                
        $file = FileItem::create(array('mimetype' => 'iimage/xoo', 'profile' => 'tussi'));
        $this->plugin->onDelete($file);
        
    }
    
    
    /**
     * @test
     */
    public function onDeleteShouldDeleteWhenPluginProvides()
    {
        $this->storage->expects($this->once())->method('deleteVersion');

        $this->plugin->setProvidesFor(array('image', 'video'));
        $this->plugin->setProfiles(array('tussi', 'lussi'));
        
        $this->plugin->setFileLib($this->filelib);
                
        $file = FileItem::create(array('mimetype' => 'image/png', 'profile' => 'tussi'));
        $this->plugin->onDelete($file);
        
    }

    
    private function createMockedTemporaryFile()
    {
        $path = ROOT_TESTS . '/data/temp/life-is-my-enemy.jpg';
        copy(ROOT_TESTS . '/data/self-lussing-manatee.jpg', $path);
        
        $this->assertFileExists($path);
    }
    
    
    
    
}
