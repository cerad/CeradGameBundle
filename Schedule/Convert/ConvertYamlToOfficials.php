<?php

namespace Cerad\Bundle\GameBundle\Schedule\Convert;

class ConvertYamlToOfficials
{
    protected $refereeCurrent = null;
    
    protected function writeAssignment($file,$assignment)
    {
        $referee = $assignment['referee'];
        $pos     = $assignment['pos'];
        $game    = $assignment['game'];
     
      //echo sprintf("Slot %s %s %s\n",$referee,$pos,$game['num']);
      //print_r($assignment); die();
        
        if (!$referee) return;
        
        if ($this->refereeCurrent != $referee)
        {
            $this->refereeCurrent = $referee;
            fputcsv($file,array());
        }
        $dt = new \DateTime($game['dtBeg']);
        
        $data = array
        (
            $referee,
            $pos,
            $game['num'],
            $dt->format('n/j/Y'),
            '',
            $dt->format('D'),
            $dt->format('g:i A'),
            $game['sportKey'],
            $game['levelKey'],
            '','',
            $game['fieldName'],
            $game['homeTeamName'],
            $game['awayTeamName'],
        );
        fputcsv($file,$data);
    }
    protected $referees = array();
    
    protected function addAssignment($referee,$pos,$game)
    {
        if (!$referee) return;

        if ($referee == 'NA') return;
        
        $data = array('referee' => $referee, 'pos' => $pos,'game' => $game);
        
        if (!isset($this->referees[$referee])) $this->referees[$referee] = array();
        
        $this->referees[$referee][] = $data;
        
        return;
    }
    public function save($fileName,$games,$teams = array())
    {
        $file = fopen($fileName,'wt');
        
        $headers = array('Referee','Pos',
            'Game','Date','Blah','DOW','Time', 'Sport','Level','Blah','Blah',
            'Site','Home-Team', 'Away-Team'
        );
        fputcsv($file,$headers);
        
        // Add all the assignments for teams being watched
        foreach($teams as $officialName => $teamNames)
        {
            foreach($teamNames as $teamName)
            {
                foreach($games as $game)
                {
                    if ($game['homeTeamName'] == $teamName) $this->addAssignment($officialName,'Watch H',$game);
                    if ($game['awayTeamName'] == $teamName) $this->addAssignment($officialName,'Watch A',$game);
                }
            }
        }
        // Add the actual assignments
        foreach($games as $game)
        {
            foreach($game['slots'] as $pos => $name)
            {
                $this->addAssignment($name,$pos,$game);
            }
        }
        ksort($this->referees);
        
        foreach($this->referees as $assignments)
        {
            usort($assignments,array($this,'sortAssignments'));
            foreach($assignments as $assignment)
            {
                $this->writeAssignment($file,$assignment);
            }
        }
        fclose($file);
    }
    public function sortAssignments($ass1,$ass2)
    {
        $dt1 = $ass1['game']['dtBeg'];
        $dt2 = $ass2['game']['dtBeg'];
        if ($dt1 < $dt2) return -1;
        if ($dt1 > $dt2) return  1;
        
        return strcmp($ass1['pos'],$ass2['pos']);
        
    }
    public function getRefereeForTeam($team)
    {
        switch($team)
        {
            case "HUNTSVILLE FUTBOL HUNTSVILLE FC BOYS '01 MAROON (A":   return 'Christopher Malone'; break;
            case 'FUSION FC FUSION 02 BOYS (AL)':                        return 'Eloy Corona'; break;
            case 'HUNTSVILLE FUTBOL 00 BOYS MAROON (AL)':                return 'Toby Linton'; break;
            
            case 'HUNTSVILLE FC GIRLS 01 RED (AL)':                      return 'John Sloan'; break;
            
            case 'HUNTSVILLE FUTBOL HFC 98 BOYS MAROON (AL)':            return 'Ralph Werling'; break;
            
            case 'HUNTSVILLE FC HFC 00 BLUE (AL)':        return 'Fred Thomas';   break;
            case 'HUNTSVILLE FUTBOL 00 BOYS MAROON (AL)': return 'Curtis Walker'; break;
            case 'HUNTSVILLE FC GIRLS 01 RED (AL))':      return 'Curtis Walker'; break;
            
            case 'CAMP FOREST FC CFFC 2K1 (TN)': return 'Adam Brooks'; break;
            
            case 'VESTAVIA ATTACK 98 BLACK (AL)': return 'John Mayer'; break;

            case '': return ''; break;
            case '': return ''; break;
        
        }
        return null;
    }
}

?>
