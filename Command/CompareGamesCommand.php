<?php

namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class CompareGamesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cerad_game__compare__games')
            ->setDescription('Compare Games')
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
        $arbFile = 'OpenCup\OpenCupArbiter20140410x.xls';
        $pssFile = 'OpenCup\OpenCupPss20140410.xlsx';
        
        $arbLoad = $this->getService('cerad_game__convert__arb_to_yaml');
        $pssLoad = $this->getService('cerad_game__convert__pss_to_yaml');
        
        $arbGames = $arbLoad->load($src . $arbFile);
        $pssGames = $pssLoad->load($src . $pssFile);
        
        file_put_contents('data/Pss.yml',Yaml::dump($arbGames,10));
        file_put_contents('data/Arb.yml',Yaml::dump($pssGames,10));
        
        echo sprintf("Games: %d %d\n",count($arbGames),count($pssGames));
        
        $compare = $this->getService('cerad_game__compare__games');
        $compare->compare($arbGames,$pssGames);
        
        return; if($input); if($output);
    }
}
?>
