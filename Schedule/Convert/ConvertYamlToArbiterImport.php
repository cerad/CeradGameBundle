<?php

namespace Cerad\Bundle\GameBundle\Schedule\Convert;

class ConvertYamlToArbiterImport
{
    public function save($fileName,$games)
    {
        $file = fopen($fileName,'wt');
        
        $headers = array('Date', 'Time', 'Game', 'Sport', 'Level', 
            'Home-Team', 'Home-Level', 'Away-Team', 'Away-Level', 
            'Site', 'Sub-site', 'Bill-To', 'Officials',
        );
        fputcsv($file,$headers);
        
        foreach($games as $game)
        {
            $parts = explode(' ',$game['dtBeg']);
            $date = trim($parts[0]);
            $time = trim($parts[1]);
            
            $projectKey = $game['projectKey'];
            $sport = null;
            if (strpos($projectKey,'HFCClassic')) $sport = 'HFC Classic';
            
            $data = array
            (
                $date,
                $time,
                $game['num'],
                $sport,
                $game['levelKey'],
                $game['homeTeamName'],
                $game['levelKey'],
                $game['awayTeamName'],
                $game['levelKey'],
                $game['venueName'],
                $game['fieldName'],
            );
            fputcsv($file,$data);
        }
        
        fclose($file);
    }
}

?>
