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
use Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator\ImprovedPathCalculator;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Version;

/**
 * @group storage
 */
class ImprovedPathCalculatorTest extends TestCase
{


    /**
     * @test
     */
    public function getsPath()
    {
        $dc = $this->getMock('Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())->method('calculateDirectoryId')->will($this->returnValue('1/2/3'));

        $pc = new ImprovedPathCalculator($dc);

        $resource = Resource::create([
            'id' => 'xooxoo'
        ]);

        $this->assertEquals(
            'resources/1/2/3/xooxoo',
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

        $pc = new ImprovedPathCalculator($dc);

        $resource = Resource::create([
            'id' => 'xooxoo'
        ]);

        $this->assertEquals(
            'resources/1/2/3/luslus/xooxoo',
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
            ->expects($this->once())
            ->method('calculateDirectoryId')
            ->will($this->returnValue('3/2/1'));

        $pc = new ImprovedPathCalculator($dc);

        $resource = Resource::create([
            'id' => 'xooxoo'
        ]);

        $file = File::create([
            'resource' => $resource,
            'id' => 'looloo'
        ]);

        $this->assertEquals(
            'files/3/2/1/luslus/looloo',
            $pc->getPathVersion($file, Version::get('luslus'))
        );
    }

    public function providePrefixes()
    {
        return [
            ['/tussi/'],
            ['nussi/'],
            ['/xussi'],
        ];
    }

    /**
     * @test
     * @dataProvider providePrefixes
     */
    public function getPathrespectsPrefixes($prefix)
    {
        $dc = $this->getMock('Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())->method('calculateDirectoryId')->will($this->returnValue('1/2/3'));

        $pc = new ImprovedPathCalculator($dc, $prefix);

        $resource = Resource::create([
            'id' => 'xooxoo'
        ]);

        $this->assertEquals(
            trim($prefix, '/') .  '/resources/1/2/3/xooxoo',
            $pc->getPath($resource)
        );
    }

    /**
     * @test
     * @dataProvider providePrefixes
     */
    public function getPathVersionRespectsPrefixes($prefix)
    {
        $dc = $this->getMock('Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())->method('calculateDirectoryId')->will($this->returnValue('1/2/3'));

        $pc = new ImprovedPathCalculator($dc, $prefix);

        $resource = Resource::create([
            'id' => 'xooxoo'
        ]);

        $this->assertEquals(
            trim($prefix, '/') . '/resources/1/2/3/luslus/xooxoo',
            $pc->getPathVersion($resource, Version::get('luslus'))
        );
    }

}
