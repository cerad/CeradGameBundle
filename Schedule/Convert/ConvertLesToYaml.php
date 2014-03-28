<?php
namespace Cerad\Bundle\GameBundle\Schedule\Convert;

use Cerad\Bundle\CoreBundle\Excel\Loader as BaseLoader;

class ConvertLesToYaml extends BaseLoader
{
    protected $record = array
    (
        'num'     => array('cols' => 'Game Number',     'req' => true),
        'date'    => array('cols' => 'Game Date',       'req' => true),
        'timeBeg' => array('cols' => 'Game Start Time', 'req' => true),
        'timeEnd' => array('cols' => 'Game End Time',   'req' => true),
        
        'flight'   => array('cols' => 'Flight Name',       'req' => true),
        'bracket'  => array('cols' => 'Bracket',           'req' => true),
        'field'    => array('cols' => 'Field Name (full)', 'req' => true),
        'type'     => array('cols' => 'Match Type',        'req' => true),
        
        'homeSeed' => array('cols' => 'Home Seed', 'req' => true),
        'homeClub' => array('cols' => 'Home Club', 'req' => true),
        'homeTeam' => array('cols' => 'Home Team Full', 'req' => true),
        
        'awaySeed' => array('cols' => 'Away Seed', 'req' => true),
        'awayClub' => array('cols' => 'Away Club', 'req' => true),
        'awayTeam' => array('cols' => 'Away Team Full', 'req' => true),
      
    );
    protected function processItem($item)
    {
        $num = $item['num'] + 6000;
        if ($num == 6000) return;
        
        $date    = $this->excel->processDate($item['date']);
        $timeBeg = $this->excel->processTime($item['timeBeg']);
        $timeEnd = $this->excel->processTime($item['timeEnd']);
                
        $level = $item['flight'];
        if (substr($level,0,2) == 'U9') $level = 'U09' . substr($level,2);
        
        $bracket = $item['bracket'];
        if (substr($bracket,0,2) == 'U9') $bracket = 'U09' . substr($bracket,2);
        
        switch($item['type'])
        {
            case 'Group Play':       $type = 'PP'; break;
            case 'Semi-Finals':      $type = 'SF'; break;
            case 'Final':            $type = 'FM'; break;
            case 'Consolation':      $type = 'CM'; break;
            case 'Consolation Game': $type = 'CM'; break;
            case 'Quarter-Finals':   $type = 'QF'; break;
            
            default: print_r($item); die("\nTYPE\n");
        }
        if (strlen($item['homeTeam'])) $homeTeam = $item['homeTeam'];
        else                           $homeTeam = $type . ' ' . $item['homeClub'];
        
        if (strlen($item['awayTeam'])) $awayTeam = $item['awayTeam'];
        else                           $awayTeam = $type . ' ' . $item['awayClub'];
        
        if ($homeTeam[0] == "'") $homeTeam = substr($homeTeam,1);
        if ($awayTeam[0] == "'") $awayTeam = substr($awayTeam,1);
        
        $site = isset($this->sites[$item['field']]) ? $this->sites[$item['field']] : null;
        if (!$site)
        {
            print_r($item); die("\nFIELD\n");
        }
        $parts = explode(',',$site);
        $venueName = trim($parts[0]);
        $fieldName = trim($parts[1]);
        
        $game = array
        (
            'num'     => $num,
            'type'    => 'Game',

            'dtBeg'   => $date . ' ' . $timeBeg,
            'dtEnd'   => $date . ' ' . $timeEnd,

            'groupKey'  => $bracket,
            'groupType' => $type,
            
            'levelKey'  => $level,
            
            'fieldName' => $fieldName,
            'venueName' => $venueName,
            
            'homeTeamName'  => $homeTeam,
            'awayTeamName'  => $awayTeam,
            
            'projectKey' => 'USSF_AL_HFCClassic_Spring2014',
            
            'slots' => $this->levels[$level]['slots'],
        );
        
        $this->items[] = $game;
        
      //echo sprintf("%4d %8s %8s %-16s %-20s %-40s %-40s\n",$num,$date,$time,$level,$site,$homeTeam,$awayTeam);
        
      //print_r($game); die("\n");
    }
    protected $levels = array
    (
        'U09 Boys Gold'   => array('slots' => 1),
        'U09 Boys Silver' => array('slots' => 1),
        'U09 Girls'       => array('slots' => 1),
        'U10 Boys 6v6'    => array('slots' => 1),
        'U10 Girls 8v8'   => array('slots' => 3),
        'U11 Girls'       => array('slots' => 3),
        'U12 Boys 8v8'    => array('slots' => 3),
        'U12 Girls 8v8'   => array('slots' => 3),
        'U13 Boys'        => array('slots' => 3),
        'U13 Girls'       => array('slots' => 3),
    );
    protected $sites = array
    (
        'Merrimack 1a Big North #M01N'   => 'Merrimack, MM01N',  
        'Merrimack 1b South Small #M01S' => 'Merrimack, MM01S',  
        
        'Merrimack 1 South #M01S'        => 'Merrimack, MM01S',
        'Merrimack 1 North #M01N'        => 'Merrimack, MM01N',
        
        'Merrimack 10 Small #M10S'       => 'Merrimack, MM10',  
        'Merrimack 10 Big #M10B'         => 'Merrimack, MM10',  
        'Merrimack 10 #M10'              => 'Merrimack, MM10',
        'Merrimack 9 Big #M09B'          => 'Merrimack, MM09',  
        
        'Merrimack 1 #M01'               => 'Merrimack, MM01',  
        'Merrimack 2 #M02'               => 'Merrimack, MM02',  
        'Merrimack 3 #M03'               => 'Merrimack, MM03',  
        'Merrimack 4 #M04'               => 'Merrimack, MM04',  
        'Merrimack 5 #M05'               => 'Merrimack, MM05',  
        'Merrimack 6 #M06'               => 'Merrimack, MM06',  
        'Merrimack 7 #M07'               => 'Merrimack, MM07',  
        'Merrimack 8 #M08'               => 'Merrimack, MM08',  
        'Merrimack 9 #M09'               => 'Merrimack, MM09',  
      
        'John Hunt 1 #JH1'        => 'John Hunt, JH01',
        'John Hunt 2 #JH2'        => 'John Hunt, JH02',
        'John Hunt 3 #JH3'        => 'John Hunt, JH03',
        'John Hunt 4 #JH4'        => 'John Hunt, JH04',
        'John Hunt 5 Small #JH5S' => 'John Hunt, JH05',
        'John Hunt 5 big #JH5B'   => 'John Hunt, JH05',
        'John Hunt 6 #JH6'        => 'John Hunt, JH06',
        
        'City South #CS' => 'City South',
        'City North #CN' => 'City North',
    );
}
?>
