<?php
namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{   
    protected function configure()
    {
        $this->setName       ('app_games:test');
        $this->setDescription('Test Repositories');
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $this->testGameRepo   ();
      //$this->testLevelRepo  ();
      //$this->testProjectRepo();
    }
    protected function testGameRepo()
    {   
        $gameRepo = $this->getService('cerad_game.game_repository');
        
        $criteria = array();
        $criteria['projectKeys'] = 'ALYS_SOCCER_NASL_FALL2013';
        
        $fields = $gameRepo->queryFieldChoices($criteria);
        print_r($fields);
        
        $criteria['levelKeys'] =  'ALYS_SOCCER_NASL_U10GIRLS, ALYS_SOCCER_NASL_U14BOYS';
       
        $teams = $gameRepo->queryTeamChoices($criteria);
        print_r($teams);
        
    }
    protected function testLevelRepo()
    {   
        $levelRepo = $this->getService('cerad_level.level_repository');
        
        $levels = $levelRepo->queryLevelChoices();
        print_r($levels);
        
        $levelKeys = $levelRepo->queryLevelKeys(array('domainSubs' => 'NASL,AHSAA'));
        print_r($levelKeys);
        
    }
    protected function testProjectRepo()
    {   
        $projectRepo = $this->getService('cerad_project.project_repository');
        
        $domains = $projectRepo->queryDomainChoices();
        print_r($domains);
        
        $domainSubs = $projectRepo->queryDomainSubChoices();
        print_r($domainSubs);
        
        $projectKeys = $projectRepo->queryProjectKeys();
        print_r($projectKeys);
        
    }
}
?>
