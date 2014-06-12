<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter\Filesystem\DirectoryIdCalculator;

use DateTime;
use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;
use Xi\Filelib\Resource\Resource;

/**
 * @group storage
 */
class TimeDirectoryIdCalculatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return array
     */
    public function provideData()
    {
        return array(
            array('1980/01/01', 'Y/m/d', '1980-01-01'),
            array('21/03/1978', 'd/m/Y', '1978-03-21'),
            array('11/11/2030/10/30/35', 'm/d/Y/H/i/s', '2030-11-11 10:30:35')
        );
    }


    /**
     * @test
     * @dataProvider provideData
     */
    public function calculateShouldCalculateCorrectly($expected, $format, $dateCreated)
    {
        $resource = Resource::create(array('date_created' => new DateTime($dateCreated)));
        $calc = new TimeDirectoryIdCalculator($format);
        $this->assertEquals($expected, $calc->calculateDirectoryId($resource));


        /*
        $this->resource->setDateCreated(new DateTime('1980-01-01'));
        $this->assertEquals("1980/01/01", $this->calc->calculateDirectoryId($this->resource));

        $this->resource->setDateCreated(new DateTime('2030-11-11 10:03:35'));
        $this->assertEquals("2030/11/11", $this->calc->calculateDirectoryId($this->resource));

        $this->calc->setFormat('m/d/Y/H/i/s');
        $this->assertEquals("11/11/2030/10/03/35", $this->calc->calculateDirectoryId($this->resource));
        */
    }

    /**
     * @test
     */
    public function defaultSettingsShouldProduceSaneDirectoryId()
    {
        $resource = Resource::create(array('date_created' => new DateTime('1978-03-21 03:03:03')));
        $calc = new TimeDirectoryIdCalculator();
        $this->assertEquals('1978/03/21', $calc->calculateDirectoryId($resource));
    }
}
