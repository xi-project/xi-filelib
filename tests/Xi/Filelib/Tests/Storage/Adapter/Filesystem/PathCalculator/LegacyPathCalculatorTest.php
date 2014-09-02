<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter\Filesystem\PathCalculator;

use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator\LegacyPathCalculator;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Version;

/**
 * @group storage
 */
class LegacyPathCalculatorTest extends TestCase
{

    /**
     * @test
     */
    public function initializes()
    {
        $pc = new LegacyPathCalculator();
        $this->assertInstanceOf(
            'Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator\LegacyPathCalculator',
            $pc
        );
    }

    /**
     * @test
     */
    public function getsPath()
    {
        $dc = $this->getMock('Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())->method('calculateDirectoryId')->will($this->returnValue('1/2/3'));

        $pc = new LegacyPathCalculator($dc);

        $resource = Resource::create([
            'id' => 'xooxoo'
        ]);

        $this->assertEquals(
            '1/2/3/xooxoo',
            $pc->getPath($resource)
        );
    }

    /**
     * @test
     */
    public function getsPathVersionForResource()
    {
        $dc = $this->getMock('Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())->method('calculateDirectoryId')->will($this->returnValue('1/2/3'));

        $pc = new LegacyPathCalculator($dc);

        $resource = Resource::create([
            'id' => 'xooxoo'
        ]);

        $this->assertEquals(
            '1/2/3/luslus/xooxoo',
            $pc->getPathVersion($resource, Version::get('luslus'))
        );
    }

    /**
     * @test
     */
    public function getsPathVersionForFile()
    {
        $dc = $this->getMock('Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc
            ->expects($this->exactly(2))
            ->method('calculateDirectoryId')
            ->will($this->onConsecutiveCalls('1/2/3', '3/2/1'));

        $pc = new LegacyPathCalculator($dc);

        $resource = Resource::create([
            'id' => 'xooxoo'
        ]);

        $file = File::create([
            'resource' => $resource,
            'id' => 'looloo'
        ]);

        $this->assertEquals(
            '1/2/3/luslus/sub/xooxoo/3/2/1/looloo',
            $pc->getPathVersion($file, Version::get('luslus'))
        );
    }
}
