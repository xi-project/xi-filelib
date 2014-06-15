<?php

namespace Xi\Filelib\Tests\Profile;

use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Profile\FileProfile;
use Xi\Filelib\File\File;
use Xi\Filelib\Events;

class ProfileManagerTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ed;

    /**
     * @var ProfileManager
     */
    private $manager;

    public function setUp()
    {
        $this->ed = $this->getMockedEventDispatcher();

        $this->manager = new ProfileManager(
            $this->ed
        );
    }

    /**
     * @test
     */
    public function hasVersionShouldDelegateToProfile()
    {
        $file = File::create(array('profile' => 'meisterlus'));

        $profile = $this->getMockedFileProfile('meisterlus');
        $profile
            ->expects($this->once())
            ->method('fileHasVersion')
            ->with(
                $file,
                'kloo'
            )
            ->will($this->returnValue(true));

        $this->manager->addProfile($profile);
        $hasVersion = $this->manager->hasVersion($file, 'kloo');
        $this->assertTrue($hasVersion);
    }

    /**
     * @test
     */
    public function getVersionProviderShouldDelegateToProfile()
    {
        $vp = $this->getMockedVersionProvider();
        $file = File::create(array('profile' => 'meisterlus'));

        $profile = $this->getMockedFileProfile('meisterlus');
        $profile
            ->expects($this->once())
            ->method('getVersionProvider')
            ->with(
                $file,
                'kloo'
            )
            ->will($this->returnValue($vp));

        $this->manager->addProfile($profile);
        $ret = $this->manager->getVersionProvider($file, 'kloo');

        $this->assertSame($vp, $ret);
    }

    /**
     * @test
     */
    public function addProfileShouldAddProfile()
    {
        $this->assertCount(0, $this->manager->getProfiles());

        $profile = $this->getMockedFileProfile('xooxer');

        $profile2 = $this->getMockedFileProfile('lusser');

        $this->ed
            ->expects($this->exactly(2))
            ->method('addSubscriber')
            ->with($this->isInstanceOf('Xi\Filelib\Profile\FileProfile'));

        $this->ed
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::PROFILE_AFTER_ADD),
                $this->isInstanceOf('Xi\Filelib\Event\FileProfileEvent')
            );

        $this->manager->addProfile($profile);
        $this->assertCount(1, $this->manager->getProfiles());

        $this->manager->addProfile($profile2);
        $this->assertCount(2, $this->manager->getProfiles());

        $this->assertSame($profile, $this->manager->getProfile('xooxer'));
        $this->assertSame($profile2, $this->manager->getProfile('lusser'));
    }

    /**
     * @test
     */
    public function addProfileShouldFailWhenProfileAlreadyExists()
    {
        $profile = new FileProfile('xooxer');
        $profile2 = new FileProfile('xooxer');

        $this->manager->addProfile($profile);
        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');
        $this->manager->addProfile($profile2);
    }

    /**
     * @test
     */
    public function getProfileShouldFailWhenProfileDoesNotExist()
    {
        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');
        $prof = $this->manager->getProfile('xooxer');
    }


}
