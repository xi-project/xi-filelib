<?php

namespace Xi\Tests\Base;


class Phaker
{
    
    public function setPuuppa($puuppa)
    {
        
    }
    
    
    public function setLoso($loso)
    {
        
    }
    
    
}



class OptionsTest extends \Xi\Tests\TestCase
{


    public function testSetOptions()
    {
        
        $mock = $this->getMock('\Xi\Tests\Base\Phaker');
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
        
        \Xi\Base\Options::setOptions($mock, $arr);
        
    }

    
    public function testSetOptionsEmpty()
    {
        $mock = $this->getMock('\Xi\Tests\Base\Phaker');
        $mock->expects($this->exactly(0))
             ->method('setPuuppa')
             ->will($this->returnValue('1'))
             ;
        
        $mock->expects($this->exactly(0))
             ->method('setLoso')
             ->will($this->returnValue('1'));
             
        $arr = array();
        
        \Xi\Base\Options::setOptions($mock, $arr);
        
    }
    
    /**
     * @expectedException \PHPUnit_Framework_Error
     */ 
    public function testSetOptionsInvalid()
    {
        $mock = $this->getMock('\Xi\Tests\Base\Phaker');
        $mock->expects($this->exactly(0))
             ->method('setPuuppa')
             ->will($this->returnValue('1'))
             ;
        
        $mock->expects($this->exactly(0))
             ->method('setLoso')
             ->will($this->returnValue('1'));
             
        $arr = array();
        
        \Xi\Base\Options::setOptions($mock, 'lussutilukset');
    }
    
    
    public function testSetConstructorOptions()
    {
        
    }
    
    
    
    
    
}
