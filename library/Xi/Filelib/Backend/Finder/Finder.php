<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Finder;

/**
 * Finder interface
 */
interface Finder
{
    /**
     * @param array $parameters Array of initial parameters
     */
    public function __construct($parameters = array());

    /**
     * Returns whether a field is allowed
     *
     * @param $field
     * @return bool
     */
    public function hasField($field);

    /**
     * Returns an array of allowed parameters
     *
     * @return array
     */
    public function getFields();

    /**
     * Adds a parameter
     *
     * @param  string          $field
     * @param  mixed           $value
     * @return Finder
     * @throws FinderException
     */
    public function addParameter($field, $value);

    /**
     * Returns parameters
     *
     * @return array
     */
    public function getParameters();

    /**
     * Returns result object class
     *
     * @return string
     */
    public function getResultClass();
}
