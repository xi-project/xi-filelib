<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Finder;

use Xi\Filelib\Backend\Finder\FinderException;

abstract class AbstractFinder implements Finder
{
    public function __construct($parameters = array())
    {
        foreach ($parameters as $key => $value) {
            $this->addParameter($key, $value);
        }
    }

    protected $fields = array();

    protected $parameters = array();

    public function getFields()
    {
        return $this->fields;
    }

    public function hasField($field)
    {
        return in_array($field, $this->getFields());
    }

    public function addParameter($field, $value)
    {
        if (!$this->hasField($field)) {
            throw new FinderException("Trying to add nonexisting field '{$field}'");
        }

        $this->parameters[$field] = $value;
        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

}
