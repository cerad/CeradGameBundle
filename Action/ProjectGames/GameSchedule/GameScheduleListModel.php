<?php
namespace Cerad\Bundle\GameBundle\Action\ProjectGames\GameSchedule;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class GameScheduleListModel extends ActionModelFactory
{
    public $project;
    public $criteria;
    
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo = $gameRepo;
    }
    public function create(Request $request)
    {   
        $this->project = $project = $request->attributes->get('project');
        
        $criteria = array();
        
        $criteria['projectKeys'] = array($project->getKey());
        
        $criteria['levels']  = array();
        $criteria['teams' ]  = array();
        $criteria['fields']  = array();
        
        $projectRole = 'tournament'; // $project->getRole();
        if ($projectRole == 'tournament')
        {
            $datesx = $project->getRawDates();
            $dates = array();
            foreach($datesx as $datex) $dates[] = $datex['date'];
            $criteria['dates'] = $dates;
        }
      //print_r($criteria);die();
        
        $this->criteria = $criteria;
        
        return $this;
        
        // Merge form session
        $session = $request->getSession();
        if ($session->has(self::SESSION_GAME_SCHEDULE_SEARCH))
        {
            $modelSession = $session->get(self::SESSION_GAME_SCHEDULE_SEARCH);
            $model = array_merge($model,$modelSession);
        }
        
        // Add in project and level ids
        $levelRepo   = $this->get('cerad_level.level_repository');
        $projectRepo = $this->get('cerad_project.project_repository');
        
        $model['levelKeys'  ] = $levelRepo->queryLevelKeys    ($model);
        $model['projectKeys'] = $projectRepo->queryProjectKeys($model);
    
        // Done
        return $model;
    }
    public function loadGames()
    {
        $this->games = $this->gameRepo->queryGameSchedule($this->criteria);
        
        return $this->games;
    }
}
