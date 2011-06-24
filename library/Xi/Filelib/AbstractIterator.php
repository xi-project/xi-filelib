<?php

namespace Xi\Filelib;

/**
 * Filelib item iterator extends ArrayIterator to implement toArray method.
 *
 * @package Xi_Filelib
 * @author pekkis
 * @todo Maybe use Doctrine's collections or some other ready-made stuff
 *
 */
abstract class AbstractIterator extends \ArrayIterator
{

    public function __construct($array)
    {
        if(!is_array($array)) {
            $array = array($array);
        }
        parent::__construct($array);
    }


    /**
     * Returns the collection as array.
     *
     * @return array
     */
    public function toArray()
    {
        $arr = array();
        foreach($this as $item) {
            $arr[] = $item->toArray();
        }

        return $arr;
    }

}
?>