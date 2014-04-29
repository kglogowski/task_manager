<?php

namespace Console\TaskBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateFilterCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('generate:filter')
                ->setDescription('Generuje filtry dla entity')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $dialog = $this->getHelperSet()->get('dialog');

        $path = $dialog->ask($output, 'Podaj ścieżke do klasy: ', '');
        $arrPath = explode('\\', $path);
        $className = $arrPath[count($arrPath) - 1];
        $lname = strtolower($className);
        
        $fs = new Filesystem();
        if(!is_dir('src/App/LibBundle/'.$className)) {
            mkdir('src/App/LibBundle/'.$className);
        }
        $fs->touch('src/App/LibBundle/'.$className.'/'.$className.'Filter.php');
        $fs->touch('src/App/LibBundle/'.$className.'/'.$className.'FilterBase.php');
        
        
    }

}
