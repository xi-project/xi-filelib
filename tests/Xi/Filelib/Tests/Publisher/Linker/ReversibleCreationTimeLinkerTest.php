<?php

/*
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Publisher\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\Publisher\Linker\ReversibleCreationTimeLinker;
use DateTime;

/**
 * @group linker
 */
class ReversibleCreationTimeLinkerTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function formatShouldBeYmsByDefault()
    {
        $linker = new ReversibleCreationTimeLinker();
        $linker->attachTo($this->getMockedFilelib());

        $this->assertSame('Y/m/d', $linker->getFormat());
    }


    public function provideData()
    {
        return array(
            array('2011/11/11/uuid-lusso-grande-lubster.tussi', '2011-11-11 10:00:00', 'uuid-lusso-grande', 'Y/m/d'),
            array('05/01/2012/uuid-tenhunen-imaiseepi-lubster.tussi', '2012-01-05 10:00:00', 'uuid-tenhunen-imaiseepi', 'd/m/Y'),
            array('05/01/2012/uuid-mehukas-ankka-loso-lubster.tussi', '2012-01-05 10:00:00', 'uuid-mehukas-ankka-loso', 'd/m/Y'),
        );
    }

    /**
     * @test
     * @dataProvider provideData
     */
    public function shouldCreateCorrectLinks($expected, $date, $uuid, $format)
    {
        $file = File::create(
            array(
                'date_created' => DateTime::createFromFormat('Y-m-d H:i:s', $date),
                'uuid' => $uuid
            )
        );
        $linker = new ReversibleCreationTimeLinker($format);

        $this->assertSame($expected, $linker->getLink($file, Version::get('lubster'), 'tussi'));
    }

    /**
     * @test
     */
    public function reversesLinks()
    {
        $fire = $this->getMockedFileRepository();
        $filelib = $this->getMockedFilelib(
            null,
            array(
                'fire' => $fire
            )
        );

        $linker = new ReversibleCreationTimeLinker(3, 100);
        $linker->attachTo($filelib);

        $file = File::create(array('uuid' => 'uuid-lusso-grande'));
        $fire
            ->expects($this->once())
            ->method('findByUuid')
            ->with('uuid-lusso-grande')
            ->will($this->returnValue($file));

        $link = '2014/11/12/uuid-lusso-grande-xoo:lusso=tussi.jpg';

        list ($reversed, $version) = $linker->reverseLink($link);

        $this->assertInstanceOf('Xi\Filelib\Plugin\VersionProvider\Version', $version);
        $expectedVersion = new Version('xoo', array('lusso' => 'tussi'));
        $this->assertSame($file, $reversed);
        $this->assertEquals($expectedVersion, $version);
    }
}
