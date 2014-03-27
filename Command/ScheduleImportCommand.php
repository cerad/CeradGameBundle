<?php
namespace Cerad\Bundle\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleImportCommand extends ContainerAwareCommand
{
    protected $commandName = 'command';
    protected $commandDesc = 'Command Description';
    
    protected function configure()
    {
        $this
            ->setName       ('app_games:import:schedule')
            ->setDescription('Schedule Import')
            ->addArgument   ('importFile', InputArgument::REQUIRED, 'Import File')
            ->addArgument   ('truncate',   InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {   
      //echo sprintf("%d\n",\PDO::ATTR_EMULATE_PREPARES); die();
        
        $importFile = $input->getArgument('importFile');
        $truncate   = $input->getArgument('truncate');
        
        if ($truncate) $truncate = true;
        
        $this->loadFile($importFile,$truncate);
    }
    /* =========================================================
     * gws/TEST_Fall2013_GamesWithSlots_20130917.xml
     * 
     * pathInfo
     *  [dirname]   => gws
     *  [basename]  => TEST_Fall2013_GamesWithSlots_20130917.xml
     *  [extension] => xml
     *  [filename]  => TEST_Fall2013_GamesWithSlots_20130917
     * 
     * parts
     * [0] => domain => TEST
     * [1] => season => Fall2013
     * [2] => format => GamesWithSlots
     * [3] => suffix => 20130917
     */
    protected function loadFile($filePath, $truncate)
    {   
        // Make sure file can be read
        if (!is_readable($filePath)) 
        {
            echo sprintf("*** File does not exist: %s\n",$filePath);
            return;
        }
        
        $pathInfo = pathinfo($filePath);
        
        $parts = explode('_',$pathInfo['filename']);
        
        if (count($parts) < 4)
        {
            echo sprintf("*** %d File Name Format: DOMAIN_Season_Format_Date\n",count($parts));
            return;
        }
        
        $paramsx = array();
        
        $paramsx['filepath'] = $filePath;
        
        $params = array_merge($paramsx,$pathInfo);
        
        $params['sport']  = 'Soccer';
        $params['domain'] = $parts[0];
        $params['season'] = $parts[1];
        $params['format'] = $parts[2];
        $params['date']   = $parts[3];
        
        $params['truncate'] = $truncate;
        
        $importServiceId = sprintf('cerad_game.schedule_Arbiter%s.import_pdo',$params['format']);
        $importService = $this->getService($importServiceId);
        
        $results = $importService->process($params);
        
        echo $results;
        
    }
}
?>
