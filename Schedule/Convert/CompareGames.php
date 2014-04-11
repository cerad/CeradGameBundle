<?php

namespace Cerad\Bundle\GameBundle\Schedule\Convert;

class CompareGames
{
    protected function gameIsDifferent($prop,$game1,$game2)
    {
        echo sprintf("==========================================\n");
        echo sprintf("Arb %s %s %s %s\n...%s %s\n",
                $game1['num'],
                $game1['dtBeg'],
                $game1['fieldName'],
                $game1['levelKey'],
                $game1['homeTeamName'],
                $game1['awayTeamName']);
        
        echo sprintf("Pss %s %s %s %s\n...%s %s\n",
                $game2['num'],
                $game2['dtBeg'],
                $game2['fieldName'],
                $game2['levelKey'],
                $game2['homeTeamName'],
                $game2['awayTeamName']);
        
        echo "Property $prop\n\n";
        
        //Debug::dump($game1);
        //Debug::dump($game2);
        // die("Property $prop\n");
    }
    /* ======================================
     * 10 Apr 2014 - Open Cup - This is not much use
     * It would the Scott changes team names and assignments
     * Without telling me.  Not worth syncing.
     */
    protected function compareTeams($prop,$game1,$game2)
    {
      //return;
        
        $team1 = $game1[$prop];
        $team2 = $game2[$prop];
        
        if ($team1 != $team2)
        {
            $this->gameIsDifferent($prop,$game1,$game2);
        }
    }
    public function compareGame($game1,$game2)
    {
        if ($game1['dtBeg'] != $game2['dtBeg']) 
        {
            $this->gameIsDifferent('DateTime',$game1,$game2);
            return;
        }
        // Problem with semi-finals
        $this->compareTeams('homeTeamName',$game1,$game2);
        $this->compareTeams('awayTeamName',$game1,$game2);
      
        if ($game1['levelKey']  != $game2['levelKey'])  
        {
            $this->gameIsDifferent('Level',$game1,$game2);
            return;
        }
        $game2Site = sprintf("%s, %s",$game2['venueName'],$game2['fieldName']);
        
        if ($game2Site != $game1['fieldName']) 
        {
            $this->gameIsDifferent('Site', $game1,$game2);
        }
    }
    public function compare($games1,$games2)
    {
        // Make sure each gets processed
        $games2Exists  = array();
        $games2Indexed = array();
        foreach($games2 as $game2) 
        { 
            $games2Indexed[$game2['num']] = $game2; 
            $games2Exists [$game2['num']] = true; 
        }
        
        foreach($games1 as $game1)
        {
            $num = $game1['num'];
            if (!isset($games2Indexed[$num]))
            {
                echo sprintf("No Pss Game for Arbiter Game %d\n",$num);
            }
            else
            {
                unset($games2Exists[$num]);
                $game2 = $games2Indexed[$num];
                
                $this->compareGame($game1,$game2);
            }
        }
        if (count($games2Exists))
        {
            echo sprintf("Pss games not found: %s\n",implode(',',array_keys($games2Exists)));
        }
        echo "### Comparison done\n";
    }
}

?>
