<?php

namespace Cerad\Bundle\GameBundle\Schedule\Convert;

class ConvertYamlToTeams
{
    protected $teams = array();
    
    // TODO: Maybe add game count
    protected function addTeam($name,$level)
    {
        if (!$name || $name == 'TBA') return;
        
        if (isset($this->teams[$name])) return;
       
        $data = array('name' => $name, 'level' => $level);
        
        $this->teams[$name] = $data;
        
        return;
    }
    public function save($fileName,$games)
    {
        $file = fopen($fileName,'wt');
        
        $headers = array('Level','Team');
        fputcsv($file,$headers);
        
        foreach($games as $game)
        {
            $this->addTeam($game['homeTeamName'],$game['levelKey']);
            $this->addTeam($game['awayTeamName'],$game['levelKey']);
        }
        ksort($this->teams);
        
        foreach($this->teams as $team)
        {
            $row = array($team['level'],$team['name']);
            fputcsv($file,$row);
        }
        fclose($file);
    }
}

?>
