<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

// Diane Stratton Format
class ConvertDstToYamlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cerad_game__convert__dst_to_yaml')
            ->setDescription('Convert Dst Schedule to Yaml')
        ;
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $base = 'A5BGames20140420a';
      //$shared = 'C:\\Users\\ahundiak\\Google Drive\\arbiter\\OpenCup\\';
        $shared = 'data/';
        
        $convert = $this->getService('cerad_game__convert__dst_to_yaml');
        
        $games = $convert->load($shared . $base . '.xlsx');
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents('data/' . $base . '.yml',Yaml::dump($games,10));
        
        $import = $this->getService('cerad_game__schedule_load');
        $import->process($games);
        
        return;
        
        $arbiterImport = $this->getService('cerad_game__convert__yaml_to_arbiter_import');
        $arbiterImport->save('data/' . $base . '.csv',$games);
        
        $teams = $this->getService('cerad_game__convert__yaml_to_teams');
        $teams->save('data/' . 'OpenCupTeams' . '.csv',$games);
        
        return; if($input); if($output);
    }
}
?>
