<?php

namespace Xi\Filelib\Plugin\Image;

use Xi\Filelib\Plugin\AbstractPlugin;
use Imagick;

/**
 * Abstract image plugin
 *
 * @author pekkis
 */
class AbstractImagePlugin extends AbstractPlugin
{

    protected $_commands = array();
    
    protected $_imageMagickOptions = array();

    
    public function addCommand(Command\Command $command)
    {
        $this->_commands[] = $command;
    }
    
    public function getCommands()
    {
        return $this->_commands;
    }
    
    public function setCommands(array $commands = array())
    {
        foreach($commands as $command)
        {
            $command = new $command['type']($command);
            $this->addCommand($command);
        }

    }
       
    /**
     * Sets ImageMagick options
     *
     * @param array $imageMagickOptions
     */
    public function setImageMagickOptions($imageMagickOptions)
    {
        $this->_imageMagickOptions = $imageMagickOptions;
    }
    

    /**
     * Return ImageMagick options
     *
     * @return array
     */
    public function getImageMagickOptions()
    {
        return $this->_imageMagickOptions;
    }

    
    public function execute($img)
    {
        foreach ($this->getImageMagickOptions() as $key => $value) {
            $method = 'set' . $key;
            $img->$method($value);
        }
        
        foreach ($this->getCommands() as $command) {
            $command->execute($img);
        }

    }
    
    
    
    /**
     * Creates a new imagick resource from path
     * 
     * @param string $path Image path
     * @return Imagick
     * @throws InvalidArgumentException
     */
    public function createImagick($path)
    {
        try {
            return new Imagick($path);
        } catch (ImagickException $e) {
            throw new InvalidArgumentException(sprintf("ImageMagick could not be created from path '%s'", $path), 500, $e);
        }
    }

        
}
