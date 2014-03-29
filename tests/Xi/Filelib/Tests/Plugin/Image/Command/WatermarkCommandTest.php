<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image\Command;

use Xi\Filelib\Tests\Plugin\Image\TestCase;
use Xi\Filelib\Plugin\Image\Command\WatermarkCommand;
use Imagick;
use InvalidArgumentException;

/**
 * @group plugin
 */
class WatermarkCommandTest extends TestCase
{

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function setWatermarkPositionShouldFailWithInvalidPosition()
    {
        $command = new WatermarkCommand('tussi', 'lus', 5);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function setWatermarkPositionShouldFailWithNonStringPosition()
    {
        $command = new WatermarkCommand('tussi', new \stdClass, 10);
    }

    public function provideDataForCoordinateCalculation()
    {
        return array(
            array(
                array('x' => 0, 'y' => 0), array(800, 600), array(100, 100), 'nw', 0,
            ),
            array(
                array('x' => 909, 'y' => 15), array(1024, 768), array(100, 20), 'ne', 15,
            ),
            array(
                array('x' => 20, 'y' => 950), array(400, 1000), array(30, 30), 'sw', 20,
            ),
            array(
                array('x' =>700, 'y' => 500), array(800, 600), array(50, 50), 'se', 50,
            ),
            array(
                array('x' => -54, 'y' => 36), array(50, 50), array(100, 10), 'se', 4,
            ),
        );
    }

    /**
     * @test
     * @dataProvider provideDataForCoordinateCalculation
     * @param array   $expected
     * @param array   $imagickO
     * @param array   $watermarkO
     * @param string  $position
     * @param integer $padding
     */
    public function calculateCoordinatesShouldCalculateCoordinatesCorrectly($expected, $imagickO, $watermarkO, $position, $padding)
    {
        $imagick = $this->getMockBuilder('\Imagick')
                        ->disableOriginalConstructor()
                        ->getMock();

        $imagick->expects($this->once())->method('getImageWidth')->will($this->returnValue($imagickO[0]));
        $imagick->expects($this->once())->method('getImageHeight')->will($this->returnValue($imagickO[1]));

        $watermark = $this->getMockBuilder('\Imagick')
                        ->disableOriginalConstructor()
                        ->getMock();

        $watermark->expects($this->once())->method('getImageWidth')->will($this->returnValue($watermarkO[0]));
        $watermark->expects($this->once())->method('getImageHeight')->will($this->returnValue($watermarkO[1]));

        $command = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')
                        ->setMethods(array('createImagick'))
                        ->setConstructorArgs(array('tussi', $position, $padding))
                        ->getMock();

        $command->expects($this->any())->method('createImagick')->will($this->returnValue($watermark));
        $ret = $command->calculateCoordinates($imagick);

        $this->assertEquals($expected, $ret);
    }

    /**
     * @test
     */
    public function getWatermarkShouldReturnImagickResourceAndCacheIt()
    {
        $watermark = $this->getMockBuilder('\Imagick')
                        ->disableOriginalConstructor()
                        ->getMock();

        $command = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')
                        ->setMethods(array('createImagick'))
                        ->setConstructorArgs(array('tussi', 'se', 7))
                        ->getMock();

        $command->expects($this->once())->method('createImagick')->will($this->returnValue($watermark));

        $res = $command->getWatermarkResource();
        $this->assertInstanceOf('\Imagick', $res);

        $res = $command->getWatermarkResource();
        $this->assertInstanceOf('\Imagick', $res);
    }

    /**
     * @test
     */
    public function destructWatermarkResourceShouldDestroyImagickResource()
    {
        $watermark = $this->getMockBuilder('\Imagick')
                        ->setMethods(array('destroy', 'clear'))
                        ->getMock();

        $watermark->expects($this->once())->method('clear');

        $command = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')
                        ->setMethods(array('createImagick'))
                        ->setConstructorArgs(array('tussi', 'se', 7))
                        ->getMock();

        $command->expects($this->once())->method('createImagick')->will($this->returnCallback(function() use ($watermark) {
            return $watermark;
        }));

        $command->getWatermarkResource();

        $command->destroyWatermarkResource();
    }

    /**
     * @test
     */
    public function destructWatermarkShouldDoNothingWhenImagickResourceDoesNotExist()
    {
        $watermark = $this->getMockBuilder('\Imagick')
                        ->setMethods(array('destroy', 'clear'))
                        ->getMock();

        $watermark->expects($this->never())->method('destroy');

        $command = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')
                        ->setMethods(array('createImagick'))
                        ->setConstructorArgs(array('tussi', 'se', 7))
                        ->getMock();

        $command->destroyWatermarkResource();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function executeShouldThrowExceptionWhenCreatingWatermarkResourceFails()
    {
        $command = new WatermarkCommand(ROOT_TESTS . '/data/illusive-manatee.jpg', 'sw', 3);

        $imagick = $this->getMockBuilder('\Imagick')
                        ->disableOriginalConstructor()
                        ->getMock();

        $command->execute($imagick);
    }

    /**
     * @test
     */
    public function executeShouldExecuteCorrectly()
    {
        $imagick = $this->getMockBuilder('\Imagick')
                        ->disableOriginalConstructor()
                        ->getMock();

        $imagick->expects($this->any())->method('getImageWidth')->will($this->returnValue(1024));
        $imagick->expects($this->any())->method('getImageHeight')->will($this->returnValue(768));

        $imagick->expects($this->once())
                 ->method('compositeImage')
                 ->with(
                     $this->isInstanceOf('\Imagick'),
                     $this->equalTo(Imagick::COMPOSITE_OVER),
                     $this->equalTo(1),
                     $this->equalTo(1)
                  );

        $watermark = $this->getMockBuilder('\Imagick')
                        ->disableOriginalConstructor()
                        ->getMock();

        $watermark->expects($this->any())->method('getImageWidth')->will($this->returnValue(100));
        $watermark->expects($this->any())->method('getImageHeight')->will($this->returnValue(10));

        $command = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')
                        ->setMethods(array('createImagick'))
                        ->setConstructorArgs(array('tussi', 'nw', 1))
                        ->getMock();

        $command->expects($this->any())->method('createImagick')->will($this->returnCallback(function() use ($watermark) {
            return $watermark;
        }));

        $command->execute($imagick);
    }
}
