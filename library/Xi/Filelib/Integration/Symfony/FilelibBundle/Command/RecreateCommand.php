<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xi\Filelib\Integration\Symfony\FilelibBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dumps assets to the filesystem.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class RecreateCommand extends ContainerAwareCommand
{

    /**
     *
     * @var Xi\Filelib\FileLibrary
     */
    private $filelib;

    protected function configure()
    {
        $this
            ->setName('filelib:recreate')
            ->setDescription('Recreates all filelib assets')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->filelib = $this->getContainer()->get('filelib');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $files = $this->filelib->file()->findAll();

        
        foreach ($files as $file) {

            $po = $file->getProfileObject();
            
            foreach ($po->getPlugins() as $plugin) {
                
                // If version plugin
                if($plugin instanceof \Xi\Filelib\Plugin\VersionProvider\VersionProvider) {

                    // and plugin is valid for the specific file's type
                    if ($plugin->providesFor($file)) {

                        $plugin->deleteVersion($file);

                        
                        $plugin->createVersion($file);
                        
                    }
                }
                
            }

            
        }
        
        
        return true;
        
        
        $output->writeln(sprintf('Dumping all <comment>%s</comment> assets.', $input->getOption('env')));
        $output->writeln(sprintf('Debug mode is <comment>%s</comment>.', $input->getOption('no-debug') ? 'off' : 'on'));
        $output->writeln('');

        if (!$input->getOption('watch')) {
            foreach ($this->am->getNames() as $name) {
                $this->dumpAsset($name, $output);
            }

            return;
        }

        if (!$this->am->isDebug()) {
            throw new \RuntimeException('The --watch option is only available in debug mode.');
        }

        $this->watch($input, $output);
    }

}
