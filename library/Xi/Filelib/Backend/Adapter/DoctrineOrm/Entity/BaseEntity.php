<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperClass
 */
abstract class BaseEntity
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="data", type="json_array")
     */
    private $data = array();

    /**
     * Get id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  array $data
     * @return File
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
