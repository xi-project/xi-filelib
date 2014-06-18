<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\Storage\Storable;

abstract class BaseStorableTestCase extends BaseIdentifiableTestCase
{
    /**
     * @return Storable
     */
    public function getInstance($args = array())
    {
        return parent::getInstance($args);
    }

    /**
     * @test
     */
    public function addVersionShouldAddVersion()
    {
        $file = $this->getInstance(
            array('data' => array('versions' => array('tussi', 'watussi')))
        );

        $file->addVersion(Version::get('lussi'));

        $this->assertEquals(array('tussi', 'watussi', 'lussi'), $file->getVersions());
    }

    /**
     * @test
     */
    public function addVersionShouldNotAddVersionIfVersionExists()
    {
        $file = $this->getInstance(
            array('data' => array('versions' => array('tussi', 'watussi')))
        );
        $file->addVersion(Version::get('watussi'));
        $this->assertEquals(array('tussi', 'watussi'), $file->getVersions());
    }

    /**
     * @test
     */
    public function removeVersionShouldRemoveVersion()
    {
        $file = $this->getInstance(
            array('data' => array('versions' => array('tussi', 'watussi')))
        );
        $file->removeVersion(Version::get('watussi'));

        $this->assertEquals(array('tussi'), $file->getVersions());
    }

    /**
     * @test
     */
    public function hasVersionShouldReturnWhetherResourceHasVersion()
    {
        $file = $this->getInstance(
            array('data' => array('versions' => array('tussi', 'watussi')))
        );

        $this->assertTrue($file->hasVersion(Version::get('tussi')));
        $this->assertTrue($file->hasVersion(Version::get('watussi')));
        $this->assertFalse($file->hasVersion(Version::get('lussi')));
    }



}
