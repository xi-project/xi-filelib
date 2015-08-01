<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Tool\PathCalculator;

use Xi\Filelib\File\File;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Tool\PathCalculator\ImprovedPathCalculator;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Versionable\Version;
use Pekkis\DirectoryCalculator\DirectoryCalculator;

class ImprovedPathCalculatorTest extends TestCase
{


    /**
     * @test
     */
    public function getsPath()
    {
        $dc = $this->getMockBuilder(DirectoryCalculator::class)->disableOriginalConstructor()->getMock();
        $dc->expects($this->any())->method('calculateDirectory')->will($this->returnValue('1/2/3'));

        $pc = new ImprovedPathCalculator($dc);

        $resource = ConcreteResource::create([
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
        $dc = $this->getMockBuilder(DirectoryCalculator::class)->disableOriginalConstructor()->getMock();
        $dc->expects($this->any())->method('calculateDirectory')->will($this->returnValue('1/2/3'));

        $pc = new ImprovedPathCalculator($dc);

        $resource = ConcreteResource::create([
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
        $dc = $this->getMockBuilder(DirectoryCalculator::class)->disableOriginalConstructor()->getMock();
        $dc
            ->expects($this->once())
            ->method('calculateDirectory')
            ->will($this->returnValue('3/2/1'));

        $pc = new ImprovedPathCalculator($dc);

        $resource = ConcreteResource::create([
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
        $dc = $this->getMockBuilder(DirectoryCalculator::class)->disableOriginalConstructor()->getMock();
        $dc->expects($this->any())->method('calculateDirectory')->will($this->returnValue('1/2/3'));

        $pc = new ImprovedPathCalculator($dc, $prefix);

        $resource = ConcreteResource::create([
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
        $dc = $this->getMockBuilder(DirectoryCalculator::class)->disableOriginalConstructor()->getMock();
        $dc->expects($this->any())->method('calculateDirectory')->will($this->returnValue('1/2/3'));

        $pc = new ImprovedPathCalculator($dc, $prefix);

        $resource = ConcreteResource::create([
            'id' => 'xooxoo'
        ]);

        $this->assertEquals(
            trim($prefix, '/') . '/resources/1/2/3/luslus/xooxoo',
            $pc->getPathVersion($resource, Version::get('luslus'))
        );
    }

}
