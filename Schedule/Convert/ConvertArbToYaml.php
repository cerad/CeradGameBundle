<?php

namespace Cerad\Bundle\GameBundle\Schedule\Convert;

use Cerad\Bundle\CoreBundle\Excel\Loader as BaseLoader;

class ConvertArbToYaml extends BaseLoader
{
    protected $record = array
    (
        'num'  => array('cols' => 'Game',        'req' => true),
        'date' => array('cols' => 'Date & Time', 'req' => true),
        'dow'  => array('cols' => 'Date & Time', 'req' => true, 'plus' => 2),
        'time' => array('cols' => 'Date & Time', 'req' => true, 'plus' => 3),
        
        'sport' => array('cols' => 'Sport & Level','req' => true),
        'level' => array('cols' => 'Sport & Level','req' => true, 'plus' => 1),
        
        'site'  => array('cols' => 'Site', 'req' => true),
        'home'  => array('cols' => 'Home', 'req' => true),
        'away'  => array('cols' => 'Away', 'req' => true),
      
        'referee' => array('cols' => 'Officials', 'req' => true),
        'ar1'     => array('cols' => 'Officials', 'req' => true, 'plus' => 1),
        'ar2'     => array('cols' => 'Officials', 'req' => true, 'plus' => 2),
    );
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        if (!$num) return;
        
        $game = array();
        $game['num'] = $num;
        $game['type'] = 'Game';
//echo sprintf("Game %d %s %s\n",$game['num'],$item['date'],$item['time']);        
        $date = new \DateTime($item['date'] . ' ' . $item['time']);
        
        $game['dtBeg'] = $date->format('Y-m-d H:i:s');
        $game['dtEnd'] = null;
        
        $game['sportKey'] = $item['sport'];
        $game['levelKey'] = $item['level'];
        
        $game['fieldName'] = $item['site'];
        
        $game['homeTeamName'] = $item['home'];
        $game['awayTeamName'] = $item['away'];
        
        $game['slots'] = array(
            'Referee' => $item['referee'],
            'AR1'     => $item['ar1'],
            'AR2'     => $item['ar2'],
        );
        $this->items[] = $game;
        return;
    }
}
?>
