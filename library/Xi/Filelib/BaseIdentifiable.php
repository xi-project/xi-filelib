<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

/**
 * Interface for identifiable objects
 */
abstract class BaseIdentifiable
{
    /**
     * @var mixed
     */
    private $id;

    /**
     * @var IdentifiableDataContainer
     */
    private $data;

    protected function __construct()
    {
        $this->data = new IdentifiableDataContainer();
    }

    /**
     * @param  mixed $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return IdentifiableDataContainer
     */
    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        if (is_array($data)) {
            $data = new IdentifiableDataContainer($data);
        }
        $this->data = $data;
        return $this;
    }

    public function __clone()
    {
        if ($this->data) {
            $this->data = clone $this->data;
        }
    }
}
