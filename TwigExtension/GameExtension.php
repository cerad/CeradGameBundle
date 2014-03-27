<?php
namespace Cerad\Bundle\GameBundle\TwigExtension;

class GameExtension extends \Twig_Extension
{
    protected $levelRepo;
    
    public function getName() { return 'cerad_game_extension'; }
    
    public function __construct($levelRepo)
    {   
        $this->levelRepo = $levelRepo;        
    }
    public function getFunctions()
    {
        return array
        (            
            'cerad_level_find'             => new \Twig_Function_Method($this, 'findLevel'),
            'cerad_game_score'             => new \Twig_Function_Method($this, 'gameScore'),
            'cerad_game_officials_summary' => new \Twig_Function_Method($this, 'gameOfficialsSummary'),
        );
    }
    public function gameScore($score)
    {
        return ($score === null) ? '_:' : $score . ':';
    }
    public function findLevel($levelKey)
    {
        return $this->levelRepo->find($levelKey);
    }
    public function gameOfficialsSummary($game)
    {
        if ($game->getStatus() == 'Canceled') return ' ';
        
        $states = array();
        $officials = $game->getOfficials();
        foreach($officials as $official)
        {
            $states[] = $official->getPersonNameFull() ? 'A' : 'O';
        }
        return implode('-',$states);
    }    
}
?>
