<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests;

use Xi\Filelib\Version;

class VersionTest extends \PHPUnit_Framework_TestCase
{
    public function provideVersionOptions()
    {
        return array(
            array(
                'tenhunen',
                array('imaiseepi' => 'pannaanin'),
                'tenhunen::imaiseepi:pannaanin'
            ),
            array(
                'tenhunen',
                array('imaiseepi' => 'pannaanin', 'anolan' => 'sankari'),
                'tenhunen::anolan:sankari;imaiseepi:pannaanin'
            ),
        );
    }

    /**
     * @test
     * @dataProvider provideVersionOptions
     */
    public function convertsToString($versionName, $options, $expected)
    {
        $version = new Version($versionName, $options);
        $this->assertEquals($expected, $version->toString());
    }

    public function provideVersionIdentifiers()
    {
        return array(
            array('tenhunen', true),
            array('tenhunen-on-numero-yksi', true),
            array('tehnunen1', true),
            array('tehnunen-1', true),
            array('tehnunen_1', true),
            array('9-tehnunen', false),
            array('_tenhusen-suuruuden-ylistyksen-versio', false),
            array('tenhusen-suuruuden-ylistyksen-versio-2_alaversion-666', true),
            array('tenhusen-suuruuden-ylistyksen-versio-2_alaver%sion-666', false),
            array('tenhusen-ylistyksen-suuruuden-alamolo', true),
            array('tenhusen-ylistyksen-suuruuden-älämölö', false),
            array('tenhusen-ylistyksen-suuruus:decibels=150', false),
            array('tenhusen-ylistyksen-suuruus::decibels:150', true),
            array('adoration-of-tenhunens-greatness::decibels:50;participants:10', true),
            // this should of course be false tho because the adoration of Tenhunen is never so quiet! :(
            array('lussogrande::tusso;nusso', false),
            array('lussogrande::tusso:;nusso', false),
            array('lussogrande::tusso;nusso:', false),
            array('lussogrande::tusso;nusso:6500', false),
            array('lussogrande::;tusso;nusso:6500', false),
            array('lussogrande::nusso:-6000;tusso:_6500', true),
            array('lussogrande::nusso:-6000;tusso:&_6500', false),
            array('x::y:1;z:0', true),
        );

    }

    /**
     * @test
     * @dataProvider provideVersionIdentifiers
     */
    public function invalidVersionsThrowUp($identifier, $valid)
    {
        if (!$valid) {
            $this->setExpectedException('Xi\Filelib\InvalidVersionException');
        }
        $version = Version::get($identifier);

        if ($valid) {
            $this->assertEquals($identifier, $version->toString());
        }

    }

    /**
     * @test
     */
    public function paramsDefaultToArray()
    {
        $version = new Version('tenhusen-egon-ylistyksen-artikkeli');
        $this->assertEquals(array(), $version->getParams());
    }

}
