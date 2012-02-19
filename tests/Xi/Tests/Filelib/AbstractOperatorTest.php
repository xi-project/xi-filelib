<?php

namespace Xi\Tests\Filelib;

use Xi\Filelib\FileLibrary;

class AbstractOperatorTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\AbstractOperator'));
    }
    
    /**
     * @test
     */
    public function getBackendShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getBackend');
        
        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();
                
        $operator->getBackend();
        
    }

    
    /**
     * @test
     */
    public function getStorageShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getStorage');
        
        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();
                
        $operator->getStorage();
        
    }

    
    /**
     * @test
     */
    public function getPublisherShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getPublisher');
        
        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();
                
        $operator->getPublisher();
        
    }

    
    /**
     * @test
     */
    public function getAclShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getAcl');
        
        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();
                
        $operator->getAcl();
    }

    
    /**
     * @test
     */
    public function getEventDispatcherShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getEventDispatcher');
        
        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();
                
        $operator->getEventDispatcher();
    }
    
    
    /**
     * @test
     */
    public function getFilelibShouldReturnFilelib()
    {
        $filelib = $this->getMockedFilelib();
                
        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();
                
        $this->assertSame($filelib, $operator->getFilelib());
        
    }

    /**
     * @test
     * @todo NO I DO NOT LIKE TESTING DEM PROTECTED POO METHODS AND WILL RETHINK IT ONCE 100% TESTS ARE ACHIEVED :)
     */
    public function fileItemToArrayShouldDelegateToFileOperator()
    {
        $filelib = new FileLibrary();
        
        $data = array(
            'luuden' => 'dorf',
            'lussen' => 'meister',
        );
        
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
               
        $fiop->expects($this->once())->method('getInstance')->with($this->equalTo($data));

        $xoo = $this->getReflectedPublicMethod('_fileItemFromArray');
        $obj = $this->getMockBuilder('Xi\Filelib\AbstractOperator')->setMethods(array())
                    ->setConstructorArgs(array($filelib))
                    ->getMockForAbstractClass();
        
        $filelib->setFileOperator($fiop);
        
        $xoo->invokeArgs($obj, array($data));
    }

    
    /**
     * @test
     * @todo NO I DO NOT LIKE TESTING DEM PROTECTED POO METHODS AND WILL RETHINK IT ONCE 100% TESTS ARE ACHIEVED :)
     */
    public function folderItemToArrayShouldDelegateToFileOperator()
    {
        $filelib = new FileLibrary();
        
        $data = array(
            'luuden' => 'dorf',
            'lussen' => 'meister',
        );
                
        $foop = $this->getMockForAbstractClass('Xi\Filelib\Folder\FolderOperator');
        $foop->expects($this->once())->method('getInstance')->with($this->equalTo($data));

        $xoo = $this->getReflectedPublicMethod('_folderItemFromArray');
        $obj = $this->getMockBuilder('Xi\Filelib\AbstractOperator')->setMethods(array())
                    ->setConstructorArgs(array($filelib))
                    ->getMockForAbstractClass();
        
        $filelib->setFolderOperator($foop);
        
        $xoo->invokeArgs($obj, array($data));
    }

    
    /**
     * @return FileLibrary
     */
    private function getMockedFilelib()
    {
        $mock = $this->getMock('Xi\Filelib\FileLibrary');
        return $mock;
    }
    
    /**
     *
     * @param string $name Method name
     * @return \ReflectionMethod
     */
    private function getReflectedPublicMethod($name)
    {
        $class = new \ReflectionClass('Xi\Filelib\AbstractOperator');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    
    
}