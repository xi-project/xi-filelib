<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

/**
 * Configurator
 *
 * @author pekkis
 *
 */
class Configurator
{


    /**
     * Sets object options via compatible setters.
     *
     * @param $object object Object to set
     * @param $options array Options to set
     */
    public static function setOptions($object, $options)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException("Non-object supplied as subject");
        }

        if (!is_array($options)) {
            throw new \InvalidArgumentException("Non-array supplied as options");
        }

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($object, $method)) {
                $object->$method($value);
            }
        }
    }

    /**
     * Sets constructor options for an object.
     *
     * @param $object object Object to set
     * @param $options mixed Options to set as zend config or array.
     */
    public static function setConstructorOptions($object, $options)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException("Non-object supplied as subject");
        }

        if (!is_array($options)) {
            throw new \InvalidArgumentException("Non-array supplied as options");
        }

        foreach ($options as $key => &$value) {
            if (is_array($value) && isset($value['class'])) {
                $classified = new $value['class'];
                self::setConstructorOptions($classified, $value['options']);
                $value = $classified;
            }
        }
        self::setOptions($object, $options);
    }


}

