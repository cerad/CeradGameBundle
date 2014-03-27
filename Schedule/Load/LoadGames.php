<?php

namespace Cerad\Bundle\GameBundle\Schedule\Load;

class LoadGames
{
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo = $gameRepo;
    }
    protected function processGame($gamex)
    {
        $num = (int)$gamex['num'];
        if (!$num) return;
        
        $levelKey   = $gamex['levelKey'];
        $projectKey = $gamex['projectKey'];
        
        $game = $this->gameRepo->findOneByProjectNum($projectKey,$num);
        if (!$game)
        {
            $game = $this->gameRepo->createGame();
            $game->setNum($num);
            $game->setStatus('Active');
            $game->setProjectKey($projectKey);
            
            $this->gameRepo->save($game);
        }
        $game->setDtBeg(new \DateTime($gamex['dtBeg']));
        $game->setDtEnd(new \DateTime($gamex['dtEnd']));
        
        $game->setGroupKey ($gamex['groupKey']);
        $game->setGroupType($gamex['groupType']);
        $game->setLevelKey ($levelKey);
        $game->setFieldName($gamex['fieldName']);
        $game->setVenueName($gamex['venueName']);
        
        $homeTeam = $game->getHomeTeam();
        $homeTeam->setName($gamex['homeTeamName']);
        $homeTeam->setLevelKey($levelKey);
        
        $awayTeam = $game->getAwayTeam();
        $awayTeam->setName($gamex['awayTeamName']);
        $awayTeam->setLevelKey($levelKey);
        
        $slots = (int)$gamex['slots'];
        for($slot = 1; $slot <= $slots; $slot++)
        {
            $official = $game->getOfficialForSlot($slot);
            if (!$official)
            {
                $official = $game->createGameOfficial();
                $official->setSlot($slot);
                switch($slot)
                {
                    case 1: $official->setRole('Referee'); break;
                    case 2: $official->setRole('AR1');     break;
                    case 3: $official->setRole('AR2');     break;
                }
                $official->setAssignState('Open');
                
                $game->addOfficial($official);
            }
        }
        $this->gameRepo->commit();
    }
    public function process($games)
    {
        echo sprintf("Loading games %d\n",count($games));
        foreach($games as $game)
        {
            $this->processGame($game);
        }
    }
}