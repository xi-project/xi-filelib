<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Configurator;

class ConfiguratorTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function setOptionsShouldSetOptions()
    {

        $mock = $this->getMock('\Xi\Filelib\File\File');
        $mock->expects($this->once())
             ->method('setName')
             ->with('looooso')
             ->will($this->returnValue('1'))
             ;

        $mock->expects($this->exactly(0))
             ->method('setMimetype')
             ->will($this->returnValue('1'));

        $arr = array(
            'mimetypeee' => 'tussi',
            'name' => 'looooso'
        );

        \Xi\Filelib\Configurator::setOptions($mock, $arr);

    }

    /**
     * @test
     */
    public function emptyOptionsShouldDoNothing()
    {
        $mock = $this->getMock('\Xi\Filelib\Tests\Phaker');
        $mock->expects($this->exactly(0))
             ->method('setName')
             ->will($this->returnValue('1'))
             ;

        $mock->expects($this->exactly(0))
             ->method('setMimetype')
             ->will($this->returnValue('1'));

        $arr = array();

        \Xi\Filelib\Configurator::setOptions($mock, $arr);

    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function setOptionsShouldThrowExceptioWithNonObjectSubject()
    {
        Configurator::setOptions('luuden', array());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function setOptionsShouldThrowExceptioWithNonArrayOptions()
    {
        $mock = $this->getMock('Xi\Filelib\File\File');
        Configurator::setOptions($mock, 'lussutilukset');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function setConstructorOptionsShouldThrowExceptioWithNonObjectSubject()
    {
        Configurator::setConstructorOptions('lus', array());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function setConstructorOptionsShouldThrowExceptioWithNonArrayOptions()
    {
        $mock = $this->getMock('Xi\Filelib\File\File');
        Configurator::setConstructorOptions($mock, 'xoo-xer');
    }

    /**
     * @test
     */
    public function constructorOptionsShouldRecurseToInnerClasses()
    {

        $mock = $this->getMockBuilder('Xi\Filelib\Tests\File\File')->setMethods(array('setName'))->getMock();

        $mock->expects($this->exactly(1))
             ->method('setName')
             ->with($this->isInstanceOf('Xi\Filelib\Tests\File\File'))
             ->will($this->returnValue('1'))
             ;

        $arr = array(
            'name' => array(
                'class' => 'Xi\Filelib\Tests\File\File',
                'options' => array(
                    'mimetype' => 'tussi',
                ),
            )

        );

        Configurator::setConstructorOptions($mock, $arr);
    }

}
