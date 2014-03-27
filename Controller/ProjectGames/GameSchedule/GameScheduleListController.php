<?php
namespace Cerad\Bundle\GameBundle\Controller\ProjectGames\GameSchedule;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

class GameScheduleListController extends Controller
{
    const SessionSearch = 'ProjectGamesGameScheduleSearch';
    
    public function listAction(Request $request, GameScheduleListModel $model)
    {
/*        
        // The form stuff
        $searchFormType = $this->get('cerad_game.game_schedule_search.form_type');
        $searchForm = $this->createForm($searchFormType,$model);
        
        $searchForm->handleRequest($request);

        if ($searchForm->isValid()) // GET Request
        {   
            $modelPosted = $searchForm->getData();
            
            $request->getSession()->set(self::SESSION_GAME_SCHEDULE_SEARCH,$modelPosted);
            
            return $this->redirect($this->generateUrl('cerad_game_schedule'));
        }
*/
        // Query for the games
        $gameRepo = $this->get('cerad_game.game_repository');
        $games = $gameRepo->queryGameSchedule($model->criteria);
        
        // And render
        $tplData = array();
      //$tplData['searchForm'] = $searchForm->createView();
        $tplData['games'] = $games;
        return $this->render($request->get('_template'),$tplData);
    }
    public function createModel(Request $request)
    {   
        // Build the search parameter information
        $model = array();
        
        $model['project'] = $project = $request->attributes->get('project');
        
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
        $model['criteria'] = $criteria;
        
        return $model;
        
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
