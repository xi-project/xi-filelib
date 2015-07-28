<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Versionable;

use Xi\Filelib\Versionable\InvalidVersionException;
use Xi\Filelib\Versionable\Version;

class VersionTest extends \PHPUnit_Framework_TestCase
{
    public function provideVersionOptions()
    {
        return array(
            array(
                'tenhunen',
                array('imaiseepi' => 'pannaanin'),
                array(),
                'tenhunen::imaiseepi:pannaanin'
            ),
            array(
                'tenhunen',
                array('imaiseepi' => 'pannaanin', 'anolan' => 'sankari'),
                array(),
                'tenhunen::anolan:sankari;imaiseepi:pannaanin'
            ),
            array(
                'retina-tenhunen',
                array('imaiseepi' => 'pannaanin', 'mahtava' => 'reso'),
                array('x2'),
                'retina-tenhunen::imaiseepi:pannaanin;mahtava:reso@x2'
            ),
            array(
                'retina-tenhunen',
                array('imaiseepi' => 'pannaanin', 'mahtava' => 'reso'),
                array('x2', 'mega-tenhunen'),
                'retina-tenhunen::imaiseepi:pannaanin;mahtava:reso@mega-tenhunen;x2'
            ),
            array(
                'modifoitu-super-kyborgi-tenhunen',
                array(),
                array('x1000'),
                'modifoitu-super-kyborgi-tenhunen@x1000'
            ),
        );
    }

    /**
     * @test
     * @dataProvider provideVersionOptions
     */
    public function convertsToString($versionName, $options, $modifiers, $expected)
    {
        $version = new Version($versionName, $options, $modifiers);
        $this->assertEquals($expected, $version->toString());
    }

    public function provideVersionIdentifiers()
    {
        return array(
            array(
                'tenhunen',
                true
            ),
            array('tenhunen-on-numero-yksi', true),
            array('tehnunen1', true),
            array('tehnunen-1', true),
            array('tehnunen_1', true),
            array('9-tehnunen', true),
            array('_9-tenhunen', false),
            array('911', true),
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
            array('lussogrande::nusso:-6000;&tusso:_6500', false),
            array('x::y:1;z:0', true),
            array('lussogrande::nusso:-6000;tusso:_6500@x2;y5', true),
            array('lussogrande::nusso:-6000;tusso:_6500@@x2:y5', false),
            array('lussogrande@x2', true),
            array('720p_webm', true),
        );

    }

    /**
     * @test
     * @dataProvider provideVersionIdentifiers
     */
    public function invalidVersionsThrowUp($identifier, $valid)
    {
        if (!$valid) {
            $this->setExpectedException(InvalidVersionException::class);
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

    /**
     * @test
     */
    public function canNotAddModifierTwice()
    {
        $version = new Version('tenhunen', array('suuruus' => 'ylistetty'), array('x5'));

        $this->assertEquals(
            array('x5'),
            $version->getModifiers()
        );

        $ret = $version->addModifier('x5');
        $this->assertEquals(
            array('x5'),
            $ret->getModifiers()
        );

        $this->assertNotSame($version, $ret);
    }

    /**
     * @test
     */
    public function removesParam()
    {
        $version = new Version('tenhunen', array('suuruus' => 'ylistetty'), array('x5'));
        $ret = $version->removeParam('suuruus');

        $this->assertEquals(array(), $ret->getParams());
        $this->assertNotSame($version, $ret);
    }

    /**
     * @test
     */
    public function addsParam()
    {
        $version = new Version('tenhunen', array('suuruus' => 'ylistetty'), array('x5'));
        $ret = $version->setParam('imaisun', 'mehevyys');

        $this->assertEquals(
            array('suuruus' => 'ylistetty', 'imaisun' => 'mehevyys'),
            $ret->getParams()
        );
        $this->assertNotSame($version, $ret);
    }

    /**
     * @test
     */
    public function replacesParam()
    {
        $version = new Version('tenhunen', array('suuruus' => 'ylistetty'), array('x5'));
        $ret = $version->setParam('suuruus', 'alistettu');

        $this->assertEquals(
            array('suuruus' => 'alistettu'),
            $ret->getParams()
        );
        $this->assertNotSame($version, $ret);
    }

    /**
     * @test
     */
    public function removesmodifier()
    {
        $version = new Version('tenhunen', array('suuruus' => 'ylistetty'), array('x5', 'x6'));

        $this->assertTrue($version->hasModifier('x5'));
        $this->assertTrue($version->hasModifier('x6'));

        $ret = $version->removeModifier('x5');

        $this->assertNotSame($version, $ret);
        $this->assertEquals(array('x6'), $ret->getModifiers());
        $this->assertFalse($ret->hasModifier('x5'));
        $this->assertTrue($ret->hasModifier('x6'));
    }

    /**
     * @test
     */
    public function setsVersion()
    {
        $version = new Version('tenhunen', array('suuruus' => 'ylistetty'), array('x5', 'x6'));
        $this->assertEquals('tenhunen', $version->getVersion());

        $ret = $version->setVersion('tenhunizer');
        $this->assertNotSame($version, $ret);
        $this->assertEquals('tenhunizer', $ret->getVersion());
    }
}
