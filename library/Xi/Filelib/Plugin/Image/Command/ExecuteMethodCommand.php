<?php

namespace Xi\Filelib\Plugin\Image\Command;

use \Imagick;


class ExecuteMethodCommand extends AbstractCommand
{
    private $_method;

    private $_parameters = array();

    
    public function setMethod($method)
    {
        $this->_method = $method;
    }
    
    
    public function getMethod()
    {
        return $this->_method;
    }
    
    
    public function setParameters($parameters)
    {
        $this->_parameters = $parameters;
    }
    
    
    public function getParameters()
    {
        return $this->_parameters;
    }
    
    
    public function execute(Imagick $img)
    {
        call_user_func_array(array($img, $this->getMethod()), $this->getParameters());
    }
    
    
}
