<?php
namespace Cerad\Bundle\GameBundle\Controller\ProjectGames\GameSchedule;

use Symfony\Component\HttpFoundation\Request;

class GameScheduleListModel
{
    public $project;
    public $criteria;
    
    public function create(Request $request)
    {   
        $this->project = $project = $request->attributes->get('project');
        
        $criteria = array();
        
        $criteria['projectKeys'] = array($project->getKey());
        
        $criteria['levels']  = array();
        $criteria['teams' ]  = array();
        $criteria['fields']  = array();
        
        $projectRole = $project->getRole();
        if ($projectRole == 'tournament')
        {
          //$criteria['dates'] = $project->getDates();
        }
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
}
