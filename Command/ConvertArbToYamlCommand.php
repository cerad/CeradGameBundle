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
      //$src = "C:\\Users\\ahundiak.IGSLAN\\Google Drive\\arbiter\\";
      //$des = "C:\\Users\\ahundiak.IGSLAN\\Google Drive\\arbiter\\";
        
        $src = "C:\\Users\\ahundiak\\Google Drive\\arbiter\\";
        $des = "C:\\Users\\ahundiak\\Google Drive\\arbiter\\";
        
      //$file = 'Classic\ClassicArbiter20140406';
        $file = 'OpenCup\OpenCupArbiter20140411';
        
        $convert = $this->getService('cerad_game__convert__arb_to_yaml');
        
        $games = $convert->load($src . $file . '.xls');
        
        echo sprintf("Games: %d\n",count($games));
        
      //file_put_contents($des . $file . 'Yml.yml',Yaml::dump($games,10));

        $teamsx = array(
          //'Adolfo Aguilar'  => array('ROCKET CITY UNITED DEVELOPMENT ACADEMY RCUDA-EAST'),
        );
        $officials = $this->getService('cerad_game__convert__yaml_to_officials');
        $officials->save($des . $file . 'ByRef.csv',$games,$teamsx);
        
        $teams = $this->getService('cerad_game__convert__yaml_to_teams');
      //$teams->save($des . $file . 'Teams.csv',$games,$teams);
        
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
