<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool;

use Closure;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\LogicException;

/**
 * Some adapters / bridges / etc might not be lazy, so we wrap them with a lazy reference resolver
 */
class LazyReferenceResolver
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @var null|string
     */
    private $expectedClass = null;

    /**
     * @var FileLibrary
     */
    private $filelib = null;

    /**
     * @param string $reference
     * @param string|null $expectedClass
     */
    public function __construct($reference, $expectedClass = null)
    {
        $this->reference = $reference;
        $this->expectedClass = $expectedClass;
    }

    /**
     * @return object
     * @throws LogicException
     */
    public function resolve()
    {
        if ($this->reference instanceof Closure) {
            $reference = $this->reference;
            $this->reference = $reference();

            if ($this->filelib) {
                $this->reference->attachTo($this->filelib);
            }
        }

        if ($this->expectedClass) {
            if (!$this->reference instanceof $this->expectedClass) {
                throw new LogicException(
                    sprintf(
                        "Expected lazy reference to resolve to class '%s', got '%s'",
                        $this->expectedClass,
                        get_class($this->reference)
                    )
                );
            }
        }

        return $this->reference;
    }

    public function getExpectedClass()
    {
        return $this->expectedClass;
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }
}
