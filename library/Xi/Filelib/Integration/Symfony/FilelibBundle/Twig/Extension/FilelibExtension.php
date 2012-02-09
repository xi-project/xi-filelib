<?php

namespace Xi\Filelib\Integration\Symfony\FilelibBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

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
    
    
    public function getFilters()
    {
        return array(
            'ceil' => new \Twig_Filter_Function('ceil'),
        );
    }
    


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'dporssi';
    }
    
    
    public function getFileUrl($file, $version = 'default')
    {
        if (is_numeric($file)) {
            $file = $this->filelib->getFileOperator()->find($id);
        }
        
        if (!$file instanceof FileItem) {
            return '';
        }        
        

        return $this->filelib->getFileOperator()->getUrl($file, array('version' => $version));
    }
    
    
}
