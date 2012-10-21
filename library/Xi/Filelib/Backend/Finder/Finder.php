<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Finder;

interface Finder
{
    public function __construct($parameters = array());

    public function hasField($field);

    public function getFields();

    public function addParameter($field, $value);

    public function getParameters();

}
