<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Plugin\Image\Command;

use Xi\Tests\Filelib\Plugin\Image\TestCase;
use Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand;
use Imagick;

class ExecuteMethodCommandTest extends TestCase
{
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $command = new ExecuteMethodCommand();

        $method = 'lussenhof';
        $this->assertEquals(null, $command->getMethod());
        $this->assertSame($command, $command->setMethod($method));
        $this->assertEquals($method, $command->getMethod());

        $arguments = array('hofen', 'lusser');
        $this->assertEquals(array(), $command->getParameters());
        $this->assertSame($command, $command->setParameters($arguments));
        $this->assertEquals($arguments, $command->getParameters());
    }

    /**
     * @test
     * @expectedException BadMethodCallException
     */
    public function executeShouldFailWhenMethodIsNotCallable()
    {
        $command = new ExecuteMethodCommand();
        $command->setMethod('cropThumbnailImagee');
        $command->setParameters(array('sometimes', 'a banana'));

        $imagick = $this->getMockBuilder('\Imagick')->disableOriginalConstructor()
                        ->setMethods(array('cropThumbnailImage'))
                        ->getMock();
        $imagick->expects($this->never())->method('cropThumbnailImage');

        $command->execute($imagick);
    }

    /**
     * @test
     */
    public function executeShouldExecuteImagemagicksMethodWhenMethodIsCallable()
    {
        $command = new ExecuteMethodCommand();
        $command->setMethod('cropThumbnailImage');
        $command->setParameters(array('sometimes', 'a banana'));

        $imagick = $this->getMockBuilder('\Imagick')->disableOriginalConstructor()
                        ->setMethods(array('cropThumbnailImage'))
                        ->getMock();
        $imagick->expects($this->once())
                ->method('cropThumbnailImage')
                ->with($this->equalTo('sometimes'), $this->equalTo('a banana'));

        $command->execute($imagick);
    }
}
