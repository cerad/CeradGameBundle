<?php
namespace Cerad\Bundle\GameBundle\Schedule\Convert;

use Cerad\Bundle\CoreBundle\Excel\Loader as BaseLoader;

class ConvertPssToYaml extends BaseLoader
{
    const GAME_NUM_OFFSET = 10000;
    
    protected $record = array
    (
        'num'     => array('cols' => 'GAMECODE', 'req' => true),
        'date'    => array('cols' => 'GMDATE',   'req' => true),
        'timeBeg' => array('cols' => 'GMTIME',   'req' => true),
        
        'complex'  => array('cols' => 'COMPLEX', 'req' => true),
        'field'    => array('cols' => 'FIELD',   'req' => true),
        
        'division' => array('cols' => 'DIVISION',  'req' => true),
        'ageGroup' => array('cols' => 'AGE_GROUP', 'req' => true),
        'gender'   => array('cols' => 'GENDER',    'req' => true),
        'phase'    => array('cols' => 'GAME_PHASE','req' => true),
        
        'homeTeam' => array('cols' => 'HOME_TEAM', 'req' => true),
        'awayTeam' => array('cols' => 'AWAY_TEAM', 'req' => true),
    );
    protected function processItem($item)
    {
        $num = $item['num'];
        if (!$num) return;
        $num += self::GAME_NUM_OFFSET;
        
        $date    = $this->excel->processDate($item['date']);
        $timeBeg = $this->excel->processTime($item['timeBeg']);
                
        $level = $this->getLevel($item['division']);
        $groupType = $this->getGroupType($item['phase']);
        
        $homeTeam = $item['homeTeam'];
        $awayTeam = $item['awayTeam'];
        
        $venueName = $this->getVenueName($item['complex']);
        $fieldName = $this->getFieldName($item['field']);
        
        $game = array
        (
            'num'     => $num,
            'type'    => 'Game',

            'dtBeg'   => $date . ' ' . $timeBeg,
            'dtEnd'   => null,

            'groupKey'  => null,
            'groupType' => $groupType,
            
            'levelKey'  => $level,
            
            'fieldName' => $fieldName,
            'venueName' => $venueName,
            
            'homeTeamName'  => $homeTeam,
            'awayTeamName'  => $awayTeam,
            'projectKey'    => 'OpenCup2014',
            
            'slots' => 3,
        );
        $this->items[] = $game;
        
      //print_r($game); die("\n");
    }
    protected function getGroupType($key)
    {
        if (isset($this->groupTypes[$key])) return $this->groupTypes[$key];
        echo sprintf("        '%s' => '%s',\n",$key,$key);
        
        return $this->groupTypes[$key] = $key;
    }
    protected $groupTypes = array
    (
        ''             => 'PP',
        'Semifinal'    => 'SF',
        'Championship' => 'FM',
        'Consolation'  => 'CM',
     );
    protected function getLevel($key)
    {
        if (isset($this->levels[$key])) return $this->levels[$key];
        echo sprintf("        '%s' => '%s',\n",$key,$key);
        
        return $this->levels[$key] = $key;
    }
    protected $levels = array
    (
        'Under 19 Boys Gold'       => 'U19B Gold',
        'Under 9 Boys Gold'        => 'U09B Gold',
        'Under 11 Boys Silver'     => 'U11B Silver',
        'Under 12 Boys 11v11 Gold' => 'U12B Gold 11v11',
        'Under 14 Boys Gold'       => 'U14B Gold',
        'Under 14 Boys Bronze'     => 'U14B Bronze',
        'Under 13 Boys Silver'     => 'U13B Silver',
        'Under 13 Boys Bronze'     => 'U13B Bronze',
        
        'Under 9 Girls Gold'       => 'U09G Gold',
        'Under 10 Girls Gold'      => 'U10G Gold',
        'Under 9 Boys Silver'      => 'U09B Silver',
        'Under 11 Boys Gold'       => 'U11B Gold',
        'Under 10 Boys Silver'     => 'U10B Silver',
        'Under 13 Girls Silver'    => 'U13G Silver',
        'Under 13 Boys Gold'       => 'U13B Gold',
        'Under 19-17  Girls Gold'  => 'U19G Gold',
        'Under 16 Boys Gold'       => 'U16B Gold',
        
        'Under 14 Girls Silver'    => 'U14G Silver',
        'Under 14 Boys Silver'     => 'U14B Silver',
        'Under 14 Girls Gold'      => 'U14G Gold',
        'Under 15 Boys Gold'       => 'U15B Gold',
        'Under 13 Girls Gold'      => 'U13G Gold',
        'Under 10 Boys Gold'       => 'U10B Gold',
        'Under 11 Girls Silver'    => 'U11G Silver',
        'Under 11 Girls Gold'      => 'U11G Gold',
        'Under 16-15 Girls Gold'   => 'U16G Gold',
        'Under 16-15 Girls Silver' => 'U16G Silver',
        'Under 13 Girls Bronze'    => 'U13G Bronze',
        'Under 12 Boys Silver'     => 'U12B Silver',
        'Under 12 Boys Gold'       => 'U12B Gold',
        'Under 17 Boys Gold'       => 'U17B Gold',
        'Under 12 Girls Gold'      => 'U12G Gold',
        'Under 12 Girls Silver'    => 'U12G Silver',
    );
    protected function getVenueName($key)
    {
        if (isset($this->venues[$key])) return $this->venues[$key];
        echo sprintf("        '%s' => '%s',\n",$key,$key);
        
        return $this->venues[$key] = $key;
    }
    protected $venues = array(
        'Jack Allen Soccer Complex'      => 'Jack Allen',
        'Point Mallard Soccer Complex'   => 'Point Mallard',
        'Wilson Morgan Athletic Complex' => 'Wilson Morgan',        
    );
    protected function getFieldName($key)
    {
        if (isset($this->fields[$key])) return $this->fields[$key];
        echo sprintf("        '%s' => '%s',\n",$key,$key);
        
        return $this->fields[$key] = $key;
    }
    protected $fields = array(
        'Jack Allen #1 (Stadium)' => 'JA01',
        'Jack Allen #2'           => 'JA02',
        'Jack Allen #3'           => 'JA03',
        'Jack Allen #4'           => 'JA04',
        'Jack Allen #5'           => 'JA05',
        'Jack Allen #6'           => 'JA06',
        'Jack Allen #7'           => 'JA07',
        'Jack Allen #8'           => 'JA08',
        'Jack Allen #9 (6v6)'     => 'JA09',
        'Jack Allen #10 (6v6)'    => 'JA10',
        'Jack Allen #11 (6v6)'    => 'JA11',
        'Jack Allen #12 (8v8)'    => 'JA12',
        'Jack Allen #13 (8v8)'    => 'JA13',
        'Jack Allen #14 (8v8)'    => 'JA14',
        'Jack Allen #15'          => 'JA15',
        'Point Mallard #1'        => 'PM01',
        'Point Mallard #2'        => 'PM02',
        'Point Mallard #3'        => 'PM03',
        'Point Mallard #4'        => 'PM04',
        'Point Mallard #5  (8v8)' => 'PM05',
        'Point Mallard #6  (6v6)' => 'PM06',
        'WM #1'                   => 'WM01',
        'WM #2 (8v8)'             => 'WM02',
    );
}
?>
