<?php
namespace Cerad\Bundle\GameBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Cerad\Bundle\CoreBundle\Events\GameEvents;

use Cerad\Bundle\CoreBundle\Event\FindGameEvent;

class GameEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            GameEvents::FindGame => array('onFindGame'),
         );
    }
    protected $gameRepositoryServiceId;
    
    public function __construct($gameRepositoryServiceId)
    {
        $this->gameRepositoryServiceId = $gameRepositoryServiceId;
    }
    protected function getGameRepository()
    {
        return $this->container->get($this->gameRepositoryServiceId);
    }
    public function onFindGame(FindGameEvent $event)
    {
        $project = $event->getProject();
        $projectKey = is_object($project) ? $project->getKey() : $project;
        
        $game = $this->getGameRepository()->findOneByProjectNum($projectKey,$event->getGameNum());
        if ($game)
        {
             $event->setGame($game);
             $event->stopPropagation();
        }
    }
}
?>
