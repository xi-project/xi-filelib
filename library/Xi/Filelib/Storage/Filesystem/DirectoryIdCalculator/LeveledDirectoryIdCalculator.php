<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

/**
 * Creates directories in a leveled hierarchy based on a numeric file id
 * 
 * @author pekkis
 *
 */
class LeveledDirectoryIdCalculator extends AbstractDirectoryIdCalculator
{

    /**
     * @var integer Files per directory
     */
    private $_filesPerDirectory = 500;

    /**
     * @var integer Levels in directory structure
     */
    private $_directoryLevels = 1;

    /**
     * Sets files per directory
     *
     * @param integer $filesPerDirectory
     * @return \Xi\Filelib\FileLibrary
     */
    public function setFilesPerDirectory($filesPerDirectory)
    {
        $this->_filesPerDirectory = $filesPerDirectory;
        return $this;
    }

    /**
     * Returns files per directory
     *
     * @return integer
     */
    public function getFilesPerDirectory()
    {
        return $this->_filesPerDirectory;
    }

    /**
     * Sets levels per directory hierarchy
     *
     * @param integer $directoryLevels
     * @return \Xi\Filelib\FileLibrary
     */
    public function setDirectoryLevels($directoryLevels)
    {
        $this->_directoryLevels = $directoryLevels;
        return $this;
    }

    /**
     * Returns levels in directory hierarchy
     *
     * @return integer
     */
    public function getDirectoryLevels()
    {
        return $this->_directoryLevels;
    }
    
    public function calculateDirectoryId(\Xi\Filelib\File\File $file)
    {
        if(!is_numeric($file->getId())) {
            throw new \Xi\Filelib\FilelibException("Leveled directory id calculator requires numeric file ids ('{$file->getId()}' was provided)");
        }
        
        $fileId = $file->getId();
        
        $directoryLevels = $this->getDirectoryLevels() + 1;
        $filesPerDirectory = $this->getFilesPerDirectory();

        if($directoryLevels < 1) {
            throw new \Xi\Filelib\FilelibException("Invalid number of directory levels ('{$directoryLevels}')");
        }

        $arr = array();
        $tmpfileid = $fileId - 1;

        for($count = 1; $count <= $directoryLevels; ++$count) {
            $lus = $tmpfileid / pow($filesPerDirectory, $directoryLevels - $count);
            $tmpfileid = $tmpfileid % pow($filesPerDirectory, $directoryLevels - $count);
            $arr[] = floor($lus) + 1;
        }

        $puuppa = array_pop($arr);
        return implode(DIRECTORY_SEPARATOR, $arr);
        
    }
    
    
    
    
}