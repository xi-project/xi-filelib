<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Integration\Symfony\FilelibBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * FilelibExtension
 *
 */
class FilelibExtension extends Extension
{

    /**
     * Loads the Monolog configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        
        /*
        set_include_path(get_include_path() . ':' . '/wwwroot/ole-filelib-demo/library');
        
        $config = new \Zend_Config_Ini('/wwwroot/ole-filelib-demo/application/configs/application.ini',
                              'development');

        $lus = $config->resources->filelib->toArray();
        
        $dumper = new \Symfony\Component\Yaml\Dumper();
        
        echo $dumper->dump($lus, 0, 4);
        
                
        
        // \Zend_Debug::dump($lus);
        
        die();
        */
        
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
                

        // die();
        
        // Backend
        
        $backend = new Definition($config['backend']['type'], array($config['backend']['options']));
        $container->setDefinition('filelib.backend', $backend);
        $backend->addMethodCall('setEntityManager', array(
            new Reference($config['backend']['key'])
        ));
        
        
        if (isset($config['backend']['folderEntity'])) {
        
           $backend->addMethodCall('setFolderEntityName', array($config['backend']['folderEntity']));
        }
        
        if (isset($config['backend']['fileEntity'])) {
            $backend->addMethodCall('setFileEntityName', array($config['backend']['fileEntity']));
        }
                
        
        // Storage
        
        // Dir id calc
        $definition = new Definition($config['storage_filesystem']['directoryIdCalculator']['type'], array($config['storage_filesystem']['directoryIdCalculator']['options']));
        $container->setDefinition('filelib.storage.directoryIdCalculator', $definition);
        
        // Storage
        $definition = new Definition('Xi\\Filelib\\Storage\\FilesystemStorage', array(array(
            'directoryPermission' => $config['storage_filesystem']['directoryPermission'],
            'filePermission' => $config['storage_filesystem']['filePermission'],
            'root' => $config['storage_filesystem']['root'],
        )));
        $container->setDefinition('filelib.storage', $definition);
        $definition->addMethodCall('setDirectoryIdCalculator', array(
            new Reference('filelib.storage.directoryIdCalculator'),
        ));
                
        
        // Publisher
        
        $definition = new Definition($config['publisher']['type'], array($config['publisher']['options']));
        $container->setDefinition('filelib.publisher', $definition);
        
        // Profiles

        
        $pc = $config['profiles'];
                       
        
        $psx = array();
                
        foreach ($pc as $p) {
            
            $definition = new Definition($p['linker']['type'], array(
                $p['linker']['options'],
            ));
            $container->setDefinition("filelib.profiles.{$p['identifier']}.linker", $definition);
            
            $definition = new Definition('Xi\\Filelib\\File\\FileProfile', array(
                array(
                    'identifier' => $p['identifier'],
                    'description' => $p['description'],
                ),
            ));
            
            $definition->addMethodCall('setLinker', array(
                new Reference("filelib.profiles.{$p['identifier']}.linker")
            ));
            
            $container->setDefinition("filelib.profiles.{$p['identifier']}", $definition);
            
            $psx[] = "filelib.profiles.{$p['identifier']}";
            
        }
        
        
        // Plugins
        
        $plugz = array();
        
        foreach ($config['plugins'] as $pluginOptions)
        {
                                    
            if (!isset($pluginOptions['profiles'])) {
                $pluginOptions['profiles'] = array_keys($this->configuration->getProfiles());
            }
            
            $definition = new Definition($pluginOptions['type'], array(
                $pluginOptions,
            ));
            $container->setDefinition("filelib.plugins.{$pluginOptions['identifier']}", $definition);

            $plugz[] = "filelib.plugins.{$pluginOptions['identifier']}";
        }
        
        
        // Acl mockin' what to actually do HELP?!?!?!
        
        $definition = new Definition('Xi\\Filelib\\Acl\\SimpleAcl');
        $container->setDefinition('filelib.acl', $definition);
                
        
        // Main
        
        $definition = new Definition('Xi\\Filelib\\FileLibrary');
        $container->setDefinition('filelib', $definition);
        
        $definition->addMethodCall('setTempDir', array(
            $config['tempDir']
        ));

        // Set backend
        $definition->addMethodCall('setBackend', array(
            new Reference('filelib.backend'),
        ));

        // Set backend
        $definition->addMethodCall('setStorage', array(
            new Reference('filelib.storage'),
        ));
        
        $definition->addMethodCall('setPublisher', array(
            new Reference('filelib.publisher'),
        ));
        
        $definition->addMethodCall('setPublisher', array(
            new Reference('filelib.publisher'),
        ));

        $definition->addMethodCall('setAcl', array(
            new Reference('filelib.acl'),
        ));
        
        foreach ($psx as $p) {
            $definition->addMethodCall('addProfile', array(new Reference($p)));
        }
        
        foreach ($plugz as $plug) {
            $definition->addMethodCall('addPlugin', array(new Reference($plug)));
        }
        
        
        
        // $definition->setFactoryClass('Xi\\Filelib\\Integration\\Symfony\\FilelibBundle\\FilelibFactory');
        // $definition->setFactoryMethod('create');
        

        // $configurator = array($this, 'luuden');
        
        // $definition->setConfigurator($configurator);
        
        
        // $definition = $container->findDefinition('filelib');
        
               
        

        
    }

}
