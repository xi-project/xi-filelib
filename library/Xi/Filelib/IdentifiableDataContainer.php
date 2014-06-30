<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

class IdentifiableDataContainer
{
    const PATTERN_NAME = '#^[a-z]([a-z0-9-_:.])*$#';

    private $data = array();

    /**
     * @param array $data
     */
    public function __construct($data = array())
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $key = $this->sanitizeInternalKey($key);
        if (!isset($this->data[$key])) {
            return $default;
        }
        return $this->data[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return IdentifiableDataContainer
     * @throws InvalidArgumentException
     */
    public function set($key, $value)
    {
        $key = $this->sanitizeInternalKey($key);
        $this->data[$key] = $value;

        return $this;
    }

    public function has($key)
    {
        $key = $this->sanitizeInternalKey($key);
        return (isset($this->data[$key]));
    }

    /**
     * @param string $key
     * @return IdentifiableDataContainer
     */
    public function delete($key)
    {
        $key = $this->sanitizeInternalKey($key);
        unset ($this->data[$key]);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @return string
     * @throws InvalidArgumentException
     */
    private function sanitizeInternalKey($key)
    {
        if (!preg_match(self::PATTERN_NAME, $key)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid key '%s",
                    $key
                )
            );
        }

        // For example MongoDB hates dots so the data container internally sanitizes some cases so that the backend
        // adapters don't have to.
        $replacers = array(
            '.' => '#'
        );

        foreach ($replacers as $source => $target) {
            $key = str_replace($source, $target, $key);
        }
        return $key;
    }
}
