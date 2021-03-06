<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssignByAssignorSlotFormType extends AbstractType
{
    public function getName() { return 'cerad_game__game_official__assign_by_assignor_slot'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cerad\Bundle\GameBundle\Entity\GameOfficial',
        ));
    }
    protected $workflow;
    protected $officialGuidOptions;
    
    public function __construct($workflow,$officials)
    {
        $this->workflow = $workflow;
        
        $guids = array();
        foreach($officials as $official)
        {
            $plan = $official->getPlan();
            $guids[$official->getGuid()] = $plan->getPersonName();
        }
        $this->officialGuidOptions = $guids;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('role', 'text', array(
            'attr'      => array('size' => 10),
            'read_only' => true,
        ));
        $builder->add('personNameFull', 'text', array(
            'attr'      => array('size' => 30),
            'read_only' => false,
            'required'  => false,
        ));
        
        $subscriber = new AssignByAssignorSlotSubscriber($builder->getFormFactory(),$this->workflow,$this->officialGuidOptions);
        $builder->addEventSubscriber($subscriber);
    }
}

