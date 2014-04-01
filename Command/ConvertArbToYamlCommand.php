<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class ConvertArbToYamlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cerad_game__convert__arb_to_yaml')
            ->setDescription('Convert Arbiter Schedule to Yaml')
        ;
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      //$shared = '$GD\\arbiter\\Classic\\';
        
        $base = 'Classic20140401ArbiterSchedule';
        
        $convert = $this->getService('cerad_game__convert__arb_to_yaml');
        
        $games = $convert->load('data/' . $base . '.xls');
        
        echo sprintf("Games: %d\n",count($games));
        
        file_put_contents('data/' . $base . '.yml',Yaml::dump($games,10));

        $teams = array(
            'Adolfo Aguilar'  => array('ROCKET CITY UNITED DEVELOPMENT ACADEMY RCUDA-EAST'),
        );
        $officials = $this->getService('cerad_game__convert__yaml_to_officials');
        $officials->save('data/Classic20140101Officials.csv',$games,$teams);
        
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
