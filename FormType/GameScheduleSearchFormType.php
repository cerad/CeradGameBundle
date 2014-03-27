<?php
namespace Cerad\Bundle\GameBundle\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GameScheduleSearchFormTypeSubscriber implements EventSubscriberInterface
{
    private $factory;
    
    private $gameRepo;
    private $levelRepo;
    private $projectRepo;
    
    public function __construct(FormFactoryInterface $factory, $projectRepo, $levelRepo, $gameRepo)
    {
        $this->factory     = $factory;
        $this->projectRepo = $projectRepo;
        $this->levelRepo   = $levelRepo;
        $this->gameRepo    = $gameRepo;
    }

    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }
    protected function genMultipleChoice($name, $choices, $width, $size = 10)
    {
        $style = sprintf('width: %d;',$width);
        
        return $this->factory->createNamed($name, 'choice', null, array(
            'label'           => false,
            'required'        => false,
            'choices'         => $choices,
            'expanded'        => false,
            'multiple'        => true,
            'disabled'        => false,
            'auto_initialize' => false,
            'attr' => array('style' => $style, 'size' => $size),
        ));
    }
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();

        if ($data === null) return;
        
        $form = $event->getForm();
        
        // Generate groups select
        $domains = $this->projectRepo->queryDomainChoices($data);
        array_unshift($domains,'All Groups');
        $form->add($this->genMultipleChoice('domains',$domains,100));
        
        // Generate sub groups select
        $domainSubs = $this->projectRepo->queryDomainSubChoices($data);
        array_unshift($domainSubs,'All Sub Groups');
        $form->add($this->genMultipleChoice('domainSubs',$domainSubs,200));
        
         // Generate levels select
        $levels = $this->levelRepo->queryLevelChoices($data);
        array_unshift($levels,'All Levels');
        $form->add($this->genMultipleChoice('levels',$levels,200));
        
        // Generate field select
        $fields = $this->gameRepo->queryFieldChoices($data);
        array_unshift($fields,'All Fields');
        $form->add($this->genMultipleChoice('fields',$fields,200));
        
         // Generate teams select
        $teams = $this->gameRepo->queryTeamChoices($data);
        array_unshift($teams,'All Teams');
        $form->add($this->genMultipleChoice('teams',$teams,200));        
    }
}

class GameScheduleSearchFormType extends AbstractType
{
    public function getName() { return 'game_schedule_search'; }
    
    protected $gameRepo;
    protected $levelRepo;
    protected $projectRepo;
    
    public function __construct($projectRepo,$levelRepo,$gameRepo)
    {
        $this->gameRepo    = $gameRepo;
        $this->levelRepo   = $levelRepo;
        $this->projectRepo = $projectRepo;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // For dynamic fields
        $subscriber = new GameScheduleSearchFormTypeSubscriber(
                $builder->getFormFactory(),
                $this->projectRepo,
                $this->levelRepo,
                $this->gameRepo);
        $builder->addEventSubscriber($subscriber);
        
        $builder->add('date1', 'date', array(
            'label'         => 'From Date',
            'input'         => 'string',
            'widget'        => 'choice',
            'format'        => 'yyyy-MMM-dd',
            'required'      => true,
        ));
        $builder->add('date2', 'date', array(
            'label'         => 'To Date',
            'input'         => 'string',
            'widget'        => 'choice',
            'format'        => 'yyyy-MMM-dd',
            'required'      => true,
        ));
        $builder->add('date1On','checkbox', array('label' => 'On', 'required' => false));
        $builder->add('date2On','checkbox', array('label' => 'On', 'required' => false));
        
        $builder->add('date1Ignore','checkbox', array('label' => 'Ignore', 'required' => false));
        $builder->add('date2Ignore','checkbox', array('label' => 'Ignore', 'required' => false));
        
        return;
        
        // Game Statuses
        $statuses = $this->params['game_statuses'];
        array_unshift($statuses,'All Status');
        
        $builder->add('statuses', 'choice', array(
            'label'         => 'Game Status',
            'required'      => false,
            'choices'       => $statuses,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 4),
        ));
        $builder->add('password','text',array(
            'label'    => 'Password',
            'required' => false,
            'attr'     => array('size' => 10),
        ));
        return;        
    }
}
?>
