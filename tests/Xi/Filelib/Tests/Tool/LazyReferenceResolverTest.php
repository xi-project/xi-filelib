<?php

namespace Xi\Filelib\Tests\Tool;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Tool\LazyReferenceResolver;

class LazyReferenceResolverTest extends TestCase
{
    /**
     * @test
     */
    public function initializesNonLazily()
    {
        $resolvee = $this->getMockedPublisher();
        $resolver = new LazyReferenceResolver($resolvee);
        $this->assertSame($resolvee, $resolver->resolve());

        $this->assertNull($resolver->getExpectedClass());
    }

    /**
     * @test
     */
    public function initializesLazily()
    {
        $resolvee = function() {
            return $this->getMockedPublisher();
        };

        $resolver = new LazyReferenceResolver($resolvee);

        $this->assertInstanceOf('Xi\Filelib\Publisher\Publisher', $resolver->resolve());
    }


    /**
     * @test
     */
    public function resolvesOnce()
    {
        $resolvee = function() {
            return $this->getMockedPublisher();
        };

        $resolver = new LazyReferenceResolver($resolvee);

        $resolved = $resolver->resolve();
        $this->assertInstanceOf('Xi\Filelib\Publisher\Publisher', $resolved);

        $this->assertSame($resolved, $resolver->resolve());
    }

    /**
     * @test
     */
    public function attachesToFilelib()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $resolvee = function() use ($filelib) {
            $p = $this->getMockedPublisher();
            $p->expects($this->once())->method('attachTo')->with($filelib);

            return $p;
        };

        $resolver = new LazyReferenceResolver($resolvee);
        $resolver->attachTo($filelib);

        $resolved = $resolver->resolve();
        $this->assertInstanceOf('Xi\Filelib\Publisher\Publisher', $resolved);

        $this->assertSame($resolved, $resolver->resolve());
    }

    /**
     * @test
     */
    public function throwsUpWhenWrongClassIsExpected()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $resolvee = function() use ($filelib) {
            $p = $this->getMockedPublisher();
            $p->expects($this->once())->method('attachTo')->with($filelib);

            return $p;
        };

        $resolver = new LazyReferenceResolver($resolvee, 'Xi\Filelib\Storage\Storage');
        $resolver->attachTo($filelib);

        $this->setExpectedException('Xi\Filelib\LogicException');
        $resolver->resolve();
    }

    /**
     * @test
     */
    public function doesntThrowUpWhenRightClassIsExpected()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $resolvee = function() use ($filelib) {
            $p = $this->getMockedPublisher();
            $p->expects($this->once())->method('attachTo')->with($filelib);

            return $p;
        };

        $resolver = new LazyReferenceResolver($resolvee, 'Xi\Filelib\Publisher\Publisher');
        $resolver->attachTo($filelib);

        $resolver->resolve();
    }

}
