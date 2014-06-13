<?php

namespace Console\TaskBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateSettingsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('generate:settings')
                ->setDescription('Generuje plik z ustawieniami')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
//        $dialog = $this->getHelperSet()->get('dialog');
//
//        $path = $dialog->ask($output, 'Podaj ścieżke do klasy: ', '');
//        $arrPath = explode('\\', $path);
//        $className = $arrPath[count($arrPath) - 1];
//        $lname = strtolower($className);
        $fs = new Filesystem();
        $fn = 'app/config/settings.yml';
        if(!is_file($fn)) {
            $fs->touch($fn);
            file_put_contents($fn, "parameters:
    mailer_available: false
            ");
        }
        
        
    }

}
