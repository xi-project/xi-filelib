<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Filelib\Backend\IdentityMapHelper;
use Xi\Filelib\Backend\Platform\Platform;
use Xi\Filelib\IdentityMap\IdentityMap;
use Xi\Filelib\IdentityMap\Identifiable;
use Xi\Filelib\File\Resource;

use Xi\Tests\Filelib\TestCase;
use ArrayIterator;

class IdentityMapHelperTest extends TestCase
{
    /**
     * @var IdentityMapHelper
     */
    private $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $im;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $platform;

    public function setUp()
    {
        $this->platform = $this->getMock('Xi\Filelib\Backend\Platform\Platform');
        $this->im = $this
            ->getMockBuilder('Xi\Filelib\IdentityMap\IdentityMap')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new IdentityMapHelper($this->im, $this->platform);
    }

    /**
     * @test
     */
    public function getIdentityMapShouldReturnIdentityMap()
    {
        $this->assertSame($this->im, $this->helper->getIdentityMap());
    }

    /**
     * @test
     */
    public function getPlatformShouldReturnPlatform()
    {
        $this->assertSame($this->platform, $this->helper->getPlatform());
    }

    /**
     * @test
     */
    public function tryOneShouldTryIdentityMapAndExitEarlyWhenFound()
    {
        $obj = Resource::create(array('id' => 1));

        $this->im
            ->expects($this->once())
            ->method('get')
            ->with(1, 'Xi\Filelib\File\Resource')
            ->will($this->returnValue($obj));

        $this->im->expects($this->never())->method('addMany');

        $ret = $this->helper->tryOneFromIdentityMap(
            1,
            'Xi\Filelib\File\Resource',
            function (Platform $platform, $id) {
                $platform->findResourcesByIds($id);
            }
        );

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);
    }


    /**
     * @test
     */
    public function tryOneShouldTryIdentityMapAndDelegateToPlatformWhenNotFound()
    {
        $obj = Resource::create(array('id' => 1));

        $this->im->expects($this->once())->method('get')->with(1, 'Xi\Filelib\File\Resource')
            ->will($this->returnValue(false));

        $this->im->expects($this->once())->method('addMany')->with($this->isInstanceOf('ArrayIterator'));

        $self = $this;
        $ret = $this
            ->helper
            ->tryOneFromIdentityMap(
                1,
                'Xi\Filelib\File\Resource',
                function (Platform $platform, $id) use ($self, $obj) {
                    $self->assertInstanceOf('Xi\Filelib\Backend\Platform\Platform', $platform);
                    $self->assertInternalType('int', $id);
                    return new ArrayIterator(array($obj));
                }
            );

        $this->assertInstanceOf('Xi\Filelib\File\Resource', $ret);
    }


    /**
     * @test
     */
    public function tryManyShouldTryManyFromIdentityMapAndExitEarlyWhenAllAreFound()
    {
        $resources = array(
            1 => Resource::create(array('id' => 1)),
            2 => Resource::create(array('id' => 2)),
            3 => Resource::create(array('id' => 3)),
            4 => Resource::create(array('id' => 4)),
            5 => Resource::create(array('id' => 5)),
        );

        $this->im
            ->expects($this->exactly(5))
            ->method('get')
            ->with($this->isType('int'), 'Xi\Filelib\File\Resource')
            ->will(
                $this->returnCallback(
                    function ($id, $class) use ($resources) {
                        return $resources[$id];
                    }
                )
            );

        $self = $this;
        $ret = $this->helper->tryManyFromIdentityMap(
            array(1, 2, 3, 4, 5),
            'Xi\Filelib\File\Resource',
            function (Platform $platform, array $ids) use ($self, $resources) {
                $self->assertInstanceOf('Xi\Filelib\Backend\Platform\Platform', $platform);
                $self->assertInternalType('array', $ids);
                $self->assertEquals(array(1, 2, 3, 4, 5), $ids);
                return new ArrayIterator($resources);
            }
        );

        $this->assertInstanceOf('ArrayIterator', $ret);
        $this->assertCount(5, $ret);
    }

    /**
     * @test
     */
    public function findResourcesByHashShouldTryManyFromIdentityMapAndDelegateToPlatformWithDiffWhenSomeAreFound()
    {
        $resources = array(
            1 => Resource::create(array('id' => 1)),
            2 => Resource::create(array('id' => 2)),
            3 => Resource::create(array('id' => 3)),
            4 => Resource::create(array('id' => 2)),
            5 => Resource::create(array('id' => 5)),
        );

        $this->im
            ->expects($this->exactly(10))
            ->method('get')
            ->with($this->isType('int'), 'Xi\Filelib\File\Resource')
            ->will(
                $this->onConsecutiveCalls(
                    $resources[1],
                    false,
                    $resources[3],
                    false,
                    $resources[5],
                    $resources[1],
                    $resources[2],
                    $resources[3],
                    $resources[4],
                    false
                )
            );

        $this->im->expects($this->once())->method('addMany')->with($this->isInstanceOf('ArrayIterator'));

        $self = $this;
        $ret = $this->helper->tryManyFromIdentityMap(
            array(1, 2, 3, 4, 5),
            'Xi\Filelib\File\Resource',
            function (Platform $platform, array $ids) use ($self, $resources) {
                $self->assertInstanceOf('Xi\Filelib\Backend\Platform\Platform', $platform);
                $self->assertInternalType('array', $ids);
                $self->assertEquals(array(2, 4), $ids);

                return new ArrayIterator(array($resources[2], $resources[4]));
            }
        );

        $this->assertInstanceOf('ArrayIterator', $ret);
        $this->assertCount(4, $ret);
    }


    /**
     * @test
     */
    public function tryAndRemoveShouldCallCallbackAndRemoveFromIdentityMap()
    {
        $obj = $this->getMock('Xi\Filelib\IdentityMap\Identifiable');
        $this->im->expects($this->once())->method('remove')->with($obj)->will($this->returnValue(true));

        $self = $this;
        $ret = $this->helper->tryAndRemoveFromIdentityMap(
            function (Platform $platform, Identifiable $obj) use ($self) {
                $self->assertInstanceOf('Xi\Filelib\Backend\Platform\Platform', $platform);
                $self->assertInstanceOf('Xi\Filelib\IdentityMap\Identifiable', $obj);
                return $obj;
            },
            $obj
        );

        $this->assertInstanceOf('Xi\Filelib\IdentityMap\Identifiable', $ret);

    }

    /**
     * @test
     */
    public function tryAndAddShouldCallCallbackWithExtraParamsAndAddToIdentityMap()
    {
        $obj = $this->getMock('Xi\Filelib\IdentityMap\Identifiable');

        $self = $this;
        $ret = $this->helper->tryAndAddToIdentityMap(
            function (Platform $platform, Identifiable $obj, $luss, $xoo) use ($self) {
                $self->assertInstanceOf('Xi\Filelib\Backend\Platform\Platform', $platform);
                $self->assertInstanceOf('Xi\Filelib\IdentityMap\Identifiable', $obj);
                $self->assertEquals('luss', $luss);
                $self->assertEquals('xoo', $xoo);
                return $obj;
            },
            $obj,
            'luss',
            'xoo'
        );

        $this->assertSame($obj, $ret);
    }
}
