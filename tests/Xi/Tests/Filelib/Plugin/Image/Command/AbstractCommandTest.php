<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Plugin\Image\Command;

use Xi\Tests\Filelib\Plugin\Image\TestCase;

/**
 * @group plugin
 */
class AbstractCommandTest extends TestCase
{
    /**
     * @test
     * @expectedException PHPUnit_Framework_Error
     */
    public function constructorShouldFailWithNonArrayOptions()
    {
        $options = 'lussuti';

        $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')
             ->setConstructorArgs($options);
    }

    /**
     * @test
     */
    public function constructorShouldPassWithArrayOptions()
    {
        $options = array('lussen' => 'hofer', 'tussen' => 'lussen');

        $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')
             ->setConstructorArgs($options);
    }

    /**
     * @test
     */
    public function createImagickShouldReturnNewImagickObject()
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')
                     ->setMethods(array('execute'))
                     ->getMock();

        $imagick = $mock->createImagick(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $this->assertInstanceOf('Imagick', $imagick);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createImagickShouldFailWithNonExistingFile()
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')
                     ->setMethods(array('execute'))
                     ->getMock();

        $imagick = $mock->createImagick(ROOT_TESTS . '/data/illusive-manatee.jpg');

        $this->assertInstanceOf('Imagick', $imagick);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createImagickShouldFailWithInvalidFile()
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')
                     ->setMethods(array('execute'))
                     ->getMock();

        $imagick = $mock->createImagick(ROOT_TESTS . '/data/20th.wav');

        $this->assertInstanceOf('\Imagick', $imagick);
    }
}
