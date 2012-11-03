<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Finder;

use Xi\Filelib\Backend\Finder\FinderException;

/**
 * Convenience class for concrete finders
 *
 * @author pekkis
 */
abstract class AbstractFinder implements Finder
{
    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $resultClass;

    /**
     * @see Finder::__construct()
     */
    public function __construct($parameters = array())
    {
        foreach ($parameters as $key => $value) {
            $this->addParameter($key, $value);
        }
    }

    /**
     * @see Finder::getResultClass()
     */
    public function getResultClass()
    {
        return $this->resultClass;
    }

    /**
     * @see Finder::getFields()
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @see Finder::hasField()
     */
    public function hasField($field)
    {
        return in_array($field, $this->getFields());
    }

    /**
     * @see Finder::addParameter()
     */
    public function addParameter($field, $value)
    {
        if (!$this->hasField($field)) {
            throw new FinderException("Trying to add nonexisting field '{$field}'");
        }

        $this->parameters[$field] = $value;
        return $this;
    }

    /**
     * @see Finder::getParameters()
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
