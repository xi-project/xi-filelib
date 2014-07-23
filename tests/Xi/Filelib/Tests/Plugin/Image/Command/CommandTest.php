<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image\Command;

use Xi\Filelib\Plugin\Image\Command\Command;
use Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand;
use Xi\Filelib\Plugin\Image\ImageMagickHelper;
use Xi\Filelib\Tests\Plugin\Image\TestCase;

/**
 * @group plugin
 */
class CommandTest extends TestCase
{
    /**
     * @test
     */
    public function addHelperReturnsSelf()
    {
        $helper = new ImageMagickHelper(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg',
            ROOT_TESTS . '/data/temp'
        );

        $command = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Image\Command\Command');
        $this->assertSame($command, $command->setHelper($helper));
    }

    /**
     * @test
     */
    public function createsExecuteMethodCommandByDefault()
    {
        $key = 0;
        $definition = ['cropThumbnailImage', [800, 200]];

        /** @var ExecuteMethodCommand $command */
        $command = Command::createCommandFromDefinition($key, $definition);
        $this->assertInstanceOf('Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand', $command);
        $this->assertEquals('cropThumbnailImage', $command->getMethod());
        $this->assertEquals([800, 200], $command->getParameters());
    }

    /**
     * @test
     */
    public function createsSpecifiedCommand()
    {
        $key = 'Xi\Filelib\Plugin\Image\Command\WatermarkCommand';
        $definition = [__DIR__ . '/watermark.png', 'se', 10];

        $command = Command::createCommandFromDefinition($key, $definition);
        $this->assertInstanceOf('Xi\Filelib\Plugin\Image\Command\WatermarkCommand', $command);
    }

    /**
     * @test
     */
    public function passesCommandThrough()
    {
        $key = 0;
        $definition = new ExecuteMethodCommand('cropThumbnailImage', [800, 200]);

        $command = Command::createCommandFromDefinition($key, $definition);
        $this->assertSame($definition, $command);
    }

    /**
     * @test
     */
    public function createsCommandsFromDefinitions()
    {
        $definitions = [
            ['cropThumbnailImage'],
            'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => [__DIR__ . '/watermark.png', 'se', 10]
        ];

        $commands = Command::createCommandsFromDefinitions($definitions);
        $this->assertCount(2, $commands);

        foreach ($commands as $command) {
            $this->assertInstanceOf('Xi\Filelib\Plugin\Image\Command\Command', $command);
        }
    }
}
