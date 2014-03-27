<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class ConvertLesToYamlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cerad_game__convert__les_to_yaml')
            ->setDescription('Convert Les Schedule to Yaml')
        ;
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $base = 'data/Classic20140327';
        
        $convert = $this->getService('cerad_game__convert__les_to_yaml');
        
        $games = $convert->load($base . '.xlsx');
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents($base . '.yml',Yaml::dump($games,10));
        
        $import = $this->getService('cerad_game__schedule_load');
        $import->process($games);
        
        return; if($input); if($output);
    }
    protected function processLesSchedule()
    {
        $loader = $this->getService('cerad_arbiter.schedule.tourn.les.load');
        
        $datax = $this->getParameter('datax');
        
        $items = $loader->load($datax . '/ScheduleLes20131014a.xlsx');
        
        echo sprintf("Games: %d\n",count($items));
        
        $saver = $this->getService('cerad_arbiter.schedule.tourn.arbiter.save');
        
        $saver->save($datax . '/ScheduleArbiter20131014a.csv',$items);
    }
}
?>
