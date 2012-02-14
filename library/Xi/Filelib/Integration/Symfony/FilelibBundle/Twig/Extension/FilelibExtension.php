<?php

namespace Xi\Filelib\Integration\Symfony\FilelibBundle\Twig\Extension;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileItem;

class FilelibExtension extends \Twig_Extension
{
    /**
     *
     * @var FileLibrary;
     */
    protected $filelib;
    
    
    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    public function getFunctions()
    {
        return array(
            'filelib_url' => new \Twig_Function_Method($this, 'getFileUrl', array('is_safe' => array('html'))),
        );
    }
    

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'filelib';
    }
    
    
    public function getFileUrl($file, $version = 'default')
    {
        if (is_numeric($file)) {
            $file = $this->filelib->getFileOperator()->find($file);
        }
        
        if (!$file instanceof FileItem) {
            throw new \InvalidArgumentException('Invalid file');
        }        
        
        return $this->filelib->getFileOperator()->getUrl($file, array('version' => $version));
    }
    
    
}
