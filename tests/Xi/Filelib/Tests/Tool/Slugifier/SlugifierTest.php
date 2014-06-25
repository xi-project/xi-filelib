<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Tool\Slugifier;

use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Tool\Slugifier\Slugifier;

class SlugifierTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var Slugifier
     */
    private $slugifier;

    public function setUp()
    {
        $this->adapter = $this->getMock('Xi\Filelib\Tool\Slugifier\Adapter\SlugifierAdapter');

        $this->slugifier = new Slugifier(
            $this->adapter
        );
    }

    /**
     * @test
     */
    public function slugifyDelegates()
    {
        $this->adapter->expects($this->once())->method('slugify')->with('tüssi')->will($this->returnValue('tussi'));

        $ret = $this->slugifier->slugify('tüssi');
        $this->assertEquals('tussi', $ret);
    }

    /**
     * @test
     */
    public function slugifyPathIterates()
    {
        $this->adapter
            ->expects($this->exactly(5))
            ->method('slugify')
            ->with('tüssi')
            ->will($this->returnValue('tussi'));

        $ret = $this->slugifier->slugifyPath('tüssi/tüssi/tüssi/tüssi/tüssi');
        $this->assertEquals('tussi/tussi/tussi/tussi/tussi', $ret);
    }

    /**
     * @test
     */
    public function defaultsToCocur()
    {
        $slugifier = new Slugifier();
        $this->assertAttributeInstanceOf(
            'Xi\Filelib\Tool\Slugifier\Adapter\CocurSlugifierAdapter',
            'adapter',
            $slugifier
        );
    }

}
