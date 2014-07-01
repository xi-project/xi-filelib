<?php

/*
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Publisher\Linker;

use Xi\Filelib\File\File;
use Xi\Filelib\Version;
use Xi\Filelib\Publisher\Linker\CreationTimeLinker;
use DateTime;

/**
 * @group linker
 */
class CreationTimeLinkerTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function formatShouldBeYmsByDefault()
    {
        $linker = new CreationTimeLinker();
        $linker->attachTo($this->getMockedFilelib());

        $this->assertSame('Y/m/d', $linker->getFormat());
    }


    public function provideData()
    {
        return array(
            array('2011/11/11/gran-lusso-lubster.tussi', '2011-11-11 10:00:00', 'gran-lusso.nom', 'Y/m/d'),
            array('05/01/2012/type-lusso-lubster.tussi', '2012-01-05 10:00:00', 'type-lusso.xoo', 'd/m/Y'),
            array('05/01/2012/xoomeister-lubster.tussi', '2012-01-05 10:00:00', 'xoomeister', 'd/m/Y'),
        );
    }

    /**
     * @test
     * @dataProvider provideData
     */
    public function shouldCreateCorrectLinks($expected, $date, $name, $format)
    {
        $file = File::create(array('date_created' => DateTime::createFromFormat('Y-m-d H:i:s', $date), 'name' => $name));
        $linker = new CreationTimeLinker($format);

        $this->assertSame($expected, $linker->getLink($file, Version::get('lubster'), 'tussi'));
    }




}
