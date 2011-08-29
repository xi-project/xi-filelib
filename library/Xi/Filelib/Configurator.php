<?php

namespace Xi\Filelib;

use \Xi\Filelib\Configuration;

/**
 * Filelib configurator
 *
 * @author pekkis
 *
 */
class Configurator
{

    /**
     * Configuration to configurate
     *
     * @var \Xi\Filelib\FileLibrary
     */
    private $config;

    public function __construct(\Xi\Filelib\FileLibrary $config)
    {
        $this->configuration = $config;
    }

    
    
    public function configurate(array $config)
    {
        if (isset($config['backend'])) {
            $this->configurateBackend($config['backend']); 
        }
        
        if (isset($config['storage'])) {
            $this->configurateStorage($config['storage']); 
        }
        
        if (isset($config['publisher'])) {
            $this->configuratePublisher($config['publisher']); 
        }
        
        if (isset($config['profiles'])) {
            array_walk($config['profiles'], array($this, 'configurateProfile'));
            // $this->configuratePublisher($config['publisher']); 
        }
        
        if (isset($config['plugins'])) {
            array_walk($config['plugins'], array($this, 'configuratePlugin'));
            // $this->configuratePlugin($config['publisher']); 
        }
        
        
    }
    


    /**
     * Configurates backend from config array
     * 
     * @param array $config
     */
    public function configurateBackend(array $config)
    {
        $backend = new $config['class']($config['options']);
        // self::setConstructorOptions($backend, $config['options']);
        $this->configuration->setBackend($backend);
    }


    /**
     * Configurates storage from config array
     * 
     * @param array $config
     */
    public function configurateStorage(array $config)
    {
        $storage = new $config['class']($config['options']);
        // self::setConstructorOptions($storage, $config['options']);
        $this->configuration->setStorage($storage);
    }

    /**
     * Configurates publisher from config array
     * 
     * @param array $config
     */
    public function configuratePublisher(array $config)
    {
        $publisher = new $config['class']($config['options']);
        // self::setConstructorOptions($publisher, $config['options']);
        $this->configuration->setPublisher($publisher);
    }


    /**
     * Configurates a plugin from config array
     * 
     * @param array $pluginOptions
     */
    public function configuratePlugin(array $pluginOptions)
    {
        // If no profiles are defined, use in all profiles.
        if (!isset($pluginOptions['profiles'])) {
            $pluginOptions['profiles'] = array_keys($this->configuration->getProfiles());
        }
        $plugin = new $pluginOptions['type']($pluginOptions);
        // self::setConstructorOptions($plugin, $pluginOptions);
        $this->configuration->addPlugin($plugin);
    }

    
    /**
     * Configurates a profile from config array
     * 
     * @param array $profileOptions
     */
    public function configurateProfile(array $profileOptions)
    {
        $profile = new \Xi\Filelib\File\FileProfile();
        self::setConstructorOptions($profile, $profileOptions);
        $this->configuration->addProfile($profile);
    }

    /**
     * Sets object options via compatible setters.
     *
     * @param $object object Object to set
     * @param $options array Options to set
     */
    public static function setOptions($object, array $options)
    {
        if (!is_object($object)) {
            return;
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
        if (!is_array($options)) {
            return;
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

