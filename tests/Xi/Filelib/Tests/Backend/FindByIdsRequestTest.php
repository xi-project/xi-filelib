<?php

namespace Xi\Filelib\Tests\Backend;

use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Events;
use PhpCollection\Sequence;

class FindByIdsRequestTest extends TestCase
{
    /**
     * @test
     */
    public function instantiates()
    {
        $ed = $this->getMockedEventDispatcher();
        $request = new FindByIdsRequest(array(1), 'Xi\Filelib\Resource\ConcreteResource', $ed);

        $this->assertEquals('Xi\Filelib\Resource\ConcreteResource', $request->getClassName());
        $this->assertEquals(array(1), $request->getNotFoundIds());
        $this->assertEquals(array(), $request->getFoundIds());
        $this->assertFalse($request->isFulfilled());
    }

    /**
     * @test
     */
    public function provideOriginModes()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @test
     * @dataProvider provideOriginModes
     */
    public function dispatchesInOriginMode($originMode)
    {
        $ed = $this->getMockedEventDispatcher();
        $request = new FindByIdsRequest(array(1), 'Xi\Filelib\Resource\ConcreteResource', $ed);

        $request->isOrigin($originMode);

        if ($originMode) {
            $ed
                ->expects($this->once())
                ->method('dispatch')
                ->with(
                    Events::IDENTIFIABLE_INSTANTIATE,
                    $this->isInstanceOf('Xi\Filelib\Event\IdentifiableEvent')
                );
        } else {
            $ed
                ->expects($this->never())
                ->method('dispatch');
        }

        $resource = ConcreteResource::create(array('id' => 1));
        $request->found($resource);

        $this->assertTrue($request->isFulfilled());
        $this->assertEquals(array(), $request->getNotFoundIds());
        $this->assertEquals(array(1), $request->getFoundIds());

        $result = $request->getResult();

        $this->assertInstanceOf('Xi\Collections\Collection\ArrayCollection', $result);
        $this->assertCount(1, $result);
        $this->assertSame($resource, $result->first());
    }

    /**
     * @test
     */
    public function resolveDelegates()
    {
        $request = new FindByIdsRequest(array(1), 'Xi\Filelib\File\File');

        $resolver = $this->getMock('Xi\Filelib\Backend\FindByIdsRequestResolver');
        $resolver
            ->expects($this->once())
            ->method('findByIds')
            ->with($request)
            ->will($this->returnArgument(0));

        $request->resolve(array($resolver));
    }

    /**
     * @test
     */
    public function foundManyIterates()
    {
        $ed = $this->getMockedEventDispatcher();
        $request = new FindByIdsRequest(array(1, 2, 3), 'Xi\Filelib\Resource\ConcreteResource', $ed);

        $iter = new Sequence(
            array(
                ConcreteResource::create(array('id' => 1)),
                ConcreteResource::create(array('id' => 3)),
            )
        );

        $request->foundMany($iter);
        $this->assertFalse($request->isFulfilled());
        $this->assertEquals(array(1, 3), array_values($request->getFoundIds()));
        $this->assertEquals(array(2), array_values($request->getNotFoundIds()));

        $this->assertCount(2, $request->getResult());
    }
}
