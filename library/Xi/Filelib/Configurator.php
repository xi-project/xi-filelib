<?php

namespace Xi\Filelib;

use \Xi\Filelib\Configuration;

/**
 * Unified object option setter. Somewhat follows a one-time ZF 2.0 proposition.
 *
 * @author pekkis
 *
 */
class Configurator
{

    /**
     * Configuration to configurate
     *
     * @var \Xi\Filelib\Configuration
     */
    private $config;

    public function __construct(\Xi\Filelib\FileLibrary $config)
    {
        $this->configuration = $config;
    }


    public static function classifier(\RecursiveArrayIterator $riter, $classify = false)
    {
        foreach ($riter as $key => $value) {
            if ($riter->hasChildren()) {
                $child = $riter->getChildren();
                if ($child->offsetExists('class')) {
                    self::classifier($child, true);

                    if ($classify) {
                        $lusser = new $child['class']();
                        self::setConstructorOptions($lusser, $child['options']);
                        \Zend_Debug::dump($lusser);


                        $riter[$key] = $lusser;

                        \Zend_Debug::dump($riter, 'riter');
                    }





                }

                self::classifier($child, true);
            }

        }


    }



    public function configurateBackend($config)
    {
        $backend = new $config['class']($config['options']);
        // self::setConstructorOptions($backend, $config['options']);
        $this->configuration->setBackend($backend);
    }


    public function configurateStorage($config)
    {
        $storage = new $config['class']($config['options']);
        // self::setConstructorOptions($storage, $config['options']);
        $this->configuration->setStorage($storage);
    }

    public function configuratePublisher($config)
    {
        $publisher = new $config['class']($config['options']);
        // self::setConstructorOptions($publisher, $config['options']);
        $this->configuration->setPublisher($publisher);
    }


    public function configuratePlugin($pluginOptions)
    {
        // If no profiles are defined, use in all profiles.
        if (!isset($pluginOptions['profiles'])) {
            $pluginOptions['profiles'] = array_keys($this->configuration->getProfiles());
        }
        $plugin = new $pluginOptions['type']($pluginOptions);
        // self::setConstructorOptions($plugin, $pluginOptions);
        $this->configuration->addPlugin($plugin);
    }



    public function configurateProfile($profileOptions)
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

