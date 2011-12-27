<?php

namespace Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\FileLibrary,
    Xi\Filelib\File\File,
    Xi\Filelib\FilelibException
    ;


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
    private $filesPerDirectory = 500;

    /**
     * @var integer Levels in directory structure
     */
    private $directoryLevels = 1;

    /**
     * Sets files per directory
     *
     * @param integer $filesPerDirectory
     * @return LeveledDirectoryCalculator
     */
    public function setFilesPerDirectory($filesPerDirectory)
    {
        $this->filesPerDirectory = $filesPerDirectory;
        return $this;
    }

    /**
     * Returns files per directory
     *
     * @return integer
     */
    public function getFilesPerDirectory()
    {
        return $this->filesPerDirectory;
    }

    /**
     * Sets levels per directory hierarchy
     *
     * @param integer $directoryLevels
     * @return LeveledDirectoryCalculator
     */
    public function setDirectoryLevels($directoryLevels)
    {
        $this->directoryLevels = $directoryLevels;
        return $this;
    }

    /**
     * Returns levels in directory hierarchy
     *
     * @return integer
     */
    public function getDirectoryLevels()
    {
        return $this->directoryLevels;
    }
    
    public function calculateDirectoryId(File $file)
    {
        if(!is_numeric($file->getId())) {
            throw new FilelibException("Leveled directory id calculator requires numeric file ids ('{$file->getId()}' was provided)");
        }
        
        $fileId = $file->getId();
        
        $directoryLevels = $this->getDirectoryLevels() + 1;
        $filesPerDirectory = $this->getFilesPerDirectory();

        if($directoryLevels < 1) {
            throw new FilelibException("Invalid number of directory levels ('{$directoryLevels}')");
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