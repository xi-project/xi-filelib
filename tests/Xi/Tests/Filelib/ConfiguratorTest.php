<?php

namespace Xi\Tests\Filelib;


class Phaker
{
    
    public function setPuuppa($puuppa)
    {
        
    }
    
    
    public function setLoso($loso)
    {
        
    }
    
    
    
    
}



class ConfiguratorTest extends \Xi\Tests\TestCase
{
    public function setUp()
    {
        
    }

    public function testSetOptions()
    {
        
        $mock = $this->getMock('\Xi\Tests\Filelib\Phaker');
        $mock->expects($this->once())
             ->method('setPuuppa')
             ->with('tussi')
             ->will($this->returnValue('1'))
             ;
        
        $mock->expects($this->exactly(0))
             ->method('setLoso')
             ->will($this->returnValue('1'));
             
        $arr = array(
            'puuppa' => 'tussi',
            'loco' => 'looooso'
        );
        
        \Xi\Filelib\Configurator::setOptions($mock, $arr);
        
    }

    
    public function testSetOptionsEmpty()
    {
        $mock = $this->getMock('\Xi\Tests\Filelib\Phaker');
        $mock->expects($this->exactly(0))
             ->method('setPuuppa')
             ->will($this->returnValue('1'))
             ;
        
        $mock->expects($this->exactly(0))
             ->method('setLoso')
             ->will($this->returnValue('1'));
             
        $arr = array();
        
        \Xi\Filelib\Configurator::setOptions($mock, $arr);
        
    }
    
    /**
     * @expectedException \PHPUnit_Framework_Error
     */ 
    public function testSetOptionsInvalid()
    {
        $mock = $this->getMock('\Xi\Tests\Filelib\Phaker');
        $mock->expects($this->exactly(0))
             ->method('setPuuppa')
             ->will($this->returnValue('1'))
             ;
        
        $mock->expects($this->exactly(0))
             ->method('setLoso')
             ->will($this->returnValue('1'));
             
        $arr = array();
        
        \Xi\Filelib\Configurator::setOptions($mock, 'lussutilukset');
    }
    
    
    public function testSetConstructorOptions()
    {
         $arr = array(
            'puuppa' => 'tussi',
         );
        
        
        $mock = $this->getMock('\Xi\Tests\Filelib\Phaker');
        $mock->expects($this->exactly(1))
             ->method('setPuuppa')
             ->with($this->isInstanceOf('\Xi\Tests\Filelib\Phaker'))
             ->will($this->returnValue('1'))
             ;

        $arr = array(
            'puuppa' => array(
                'class' => '\\Xi\\Tests\\Filelib\\Phaker',
                'options' => array(
                    'loso' => 'tussi',
                ),
            )
            
        );
        
        \Xi\Filelib\Configurator::setConstructorOptions($mock, $arr);
        
    }
    
    
    
    
    
}
