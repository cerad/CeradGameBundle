<?php
namespace Cerad\Bundle\GameBundle\Schedule\Import;

/* ===================================================
 * Clean database
 * $ ./console app_games:import:schedule data/ALYS_20131218_Fall2013_GamesWithSlots.xml
   Arbiter Import  ALYS_20131218_Fall2013_GamesWithSlots.xml
   Games Total 3314, Insert 3297, Update 0
   Duration 10.94 83.36M
 * 
 * Existing database no updates
 * Arbiter Import  ALYS_20131218_Fall2013_GamesWithSlots.xml
   Games Total 3314, Insert 0, Update 0
   Duration 5.57 63.44M
 * 
 * Wonder why I had trouble on zayso doing a complete import?
 * 
 * Does PDO provide any significant speedup?
 * 
 * Duration 10.97 83.36M Removed project repo
 * Duration 10.33 83.36M Local   project cache
 * 
 * Duration  9.97 85.46M Removed level repo, added level cache
 * 
 * Duration  5.24  8.65M Inserted games but no teams
 * 
 * Duration 14.57  8.65M Inserted game teams, less memory but much longer executation?
 * 
 * Duration  1.59  8.65M Wrap everything in a transaction and it flies
Arbiter Import  ALYS_Fall2013_GamesWithSlots_20131218.xml
Games Tot 3314 Insert: 3297, Update: 0
Game Teams     Insert: 6594, Update: 0
Game Officials Insert: 8001, Update: 0, Delete: 0
Project  Teams Insert: 1894
Duration 4.20 6.03M

Arbiter Import  ALYS_Fall2013_GamesWithSlots_20131218.xml
Games Tot 3314 Insert: 0, Update: 0
Game Teams     Insert: 0, Update: 0
Game Officials Insert: 0, Update: 0, Delete: 0
Project  Teams Insert: 0
Duration 1.98 6.03M
 * 
Arbiter Import  ALYS_Fall2013_GamesWithSlots_20131218.xml
Games Tot 3314 Insert: 3297, Update: 0
Game Teams     Insert: 6594, Update: 0
Game Officials Insert: 8001, Update: 0, Delete: 0
Project  Teams Insert: 1894
Duration 3.90 6.03M
 */
class ArbiterGamesWithSlotsImportPDO
{
    protected $helper;
    
    protected $results;
   
    protected $sport;
    protected $season;
    protected $domain;
    
    public function __construct($helper)
    {
        $this->helper = $helper;
        
        $this->results = new ArbiterImportResults();
    }
    /* =========================================================
     * Generic semi_readable hash
     */
    protected function hash($params)
    {
        // Trim and cat
        $value = implode('_',$params);
      //array_walk($params, function($val) use (&$value) { $value .= trim($val) . '_'; });
        
        return strtoupper(str_replace(array(' ','~','-',"'"),'',$value));
        
      //return substr($valuex,0,strlen($valuex) - 1);
    }
    /* ==========================================================
     * Project
     */
    protected function processProject($row)
    {
        // Hash it
        $hashParams = array($row['domain'],$row['sport'],$row['domainSub'],$row['season']);
        $projectKey = $this->hash($hashParams);
        
        $selectParams = array('key' => $projectKey);
        
        $stmt = $this->helper->prepareProjectSelect();
        $stmt->execute($selectParams);
        $rows = $stmt->fetchAll();
        
        if (count($rows)) return $projectKey;
        
        $insertParams = array
        (
            'key'       => $projectKey,
            'season'    => $row['season'],
            'sport'     => $row['sport'],
            'domain'    => $row['domain'],
            'domainSub' => $row['domainSub'],
        );
        $this->helper->prepareProjectInsert()->execute($insertParams);
        
        return $projectKey;        
    }
    /* ===============================================================
     * Level
     */
    protected function processLevel($row)
    {
        // Hash it
        $hashParams = array($row['domain'],$row['sport'],$row['domainSub'],$row['level']);
        
        $levelKey = $this->hash($hashParams);
        
        $selectParams = array('key' => $levelKey);
        
        $stmt = $this->helper->prepareLevelSelect();
        $stmt->execute($selectParams);
        $rows = $stmt->fetchAll();
        
        if (count($rows)) return $levelKey;
        
        $insertParams = array
        (
            'key'       => $levelKey,
            'name'      => $row['level'],
            'sport'     => $row['sport'],
            'domain'    => $row['domain'],
            'domainSub' => $row['domainSub'],
        );
        $this->helper->prepareLevelInsert()->execute($insertParams);
        
        return $levelKey;        
    }
    /* ============================================
     * Project Teams
     */
    protected function processProjectTeam($projectKey,$levelKey,$name)
    {
        // See if one exists
        $params = array
        (
            'projectKey' => $projectKey,
            'levelKey'   => $levelKey,
            'name'       => $name,
        );
        $selectStmt = $this->helper->prepareProjectTeamSelect();
        $selectStmt->execute($params);
        $rows = $selectStmt->fetchAll();
        if (count($rows)) return;
        
        // Insert it
        $this->helper->prepareProjectTeamInsert()->execute($params);
        $this->results->countProjectTeamsInsert++;
    }
    /* =================================================================
     * Queries
     */
    protected function queryGame($projectKey,$num)
    {
        $stmt = $this->helper->prepareGameSelect();
        $stmt->execute(array('projectKey' => $projectKey, 'num' => $num));
        
        $rows = $stmt->fetchAll();
        
        return count($rows) ? $rows[0] : null;
    }
    protected function queryGameTeams($gameId)
    {
        $items = array();
        
        $stmt = $this->helper->prepareGameTeamsSelect();
        $stmt->execute(array('gameId' => $gameId));
        
        $rows = $stmt->fetchAll();
        
        foreach($rows as $row)
        {   
            $items[$row['slot']] = $row;
        }
        // Index by slot?
        return $items;
    }
   protected function queryGameOfficials($gameId)
    {
        $items = array();
        
        $stmt = $this->helper->prepareGameOfficialsSelect();
        $stmt->execute(array('gameId' => $gameId));
        
        $rows = $stmt->fetchAll();
        
        foreach($rows as $row)
        {
            $items[$row['slot']] = $row;
        }
        return $items;
    }
    protected function insertGame($projectKey,$num,$levelKey,$row)
    {
        $gameParams = array
        (
            'projectKey' => $projectKey,
            'num'        => $num,
            'levelKey'   => $levelKey,
            'fieldName'  => $row['fieldName'],
            'venueName'  => $row['venueName'],
            'dtBeg'      => $row['dtBeg' ],
            'dtEnd'      => $row['dtEnd' ],
            'status'     => $row['status'],
        );
        $this->helper->prepareGameInsert()->execute($gameParams);
        $this->results->countGamesInsert++;
        
        $gameId = $this->helper->lastInsertId();
        
        foreach($row['teams'] as $team)
        {
            $team['gameId']   = $gameId;
            $team['levelKey'] = $levelKey;
            
            $this->helper->prepareGameTeamInsert()->execute($team);
            $this->results->countGameTeamsInsert++;
        }     
        
        // Insert Officials
        foreach($row['officials'] as $official)
        {
            $official['gameId'] = $gameId;
            $this->helper->prepareGameOfficialInsert()->execute($official);
            $this->results->countGameOfficialsInsert++;
        }
    }
    protected function updateGame($levelKey,$game,$row)
    {
        // See if game needs updating
        $needUpdate = false;
        if ($game['levelKey']  != $levelKey)         { $game['levelKey']  = $levelKey;         $needUpdate = true; }
        if ($game['fieldName'] != $row['fieldName']) { $game['fieldName'] = $row['fieldName']; $needUpdate = true; }
        if ($game['venueName'] != $row['venueName']) { $game['venueName'] = $row['venueName']; $needUpdate = true; }
        if ($game['dtBeg']     != $row['dtBeg'])     { $game['dtBeg']     = $row['dtBeg'];     $needUpdate = true; }
        if ($game['dtEnd']     != $row['dtEnd'])     { $game['dtEnd']     = $row['dtEnd'];     $needUpdate = true; }
        if ($game['status']    != $row['status'])    { $game['status']    = $row['status'];    $needUpdate = true; }
        
        if ($needUpdate)
        {
            $this->helper->prepareGameUpdate()->execute($game);
            $this->results->countGamesUpdate++;
        }
        return $game;
    }
    protected function updateGameTeam($levelKey,$gameTeam,$rowTeam)
    {
        // See if game needs updating
        $needUpdate = false;
        
        if ($gameTeam['levelKey'] != $levelKey) 
        { 
            $gameTeam['levelKey']  = $levelKey; 
            $needUpdate = true;
        }
        if ($gameTeam['name'] != $rowTeam['name'])
        { 
            $gameTeam['name']  = $rowTeam['name'];
            $needUpdate = true;
        }
        if ($gameTeam['score'] !== $rowTeam['score'])
        { 
            $gameTeam['score']   = $rowTeam['score'];
            $needUpdate = true;  
        }
        if ($needUpdate)
        {
            $this->helper->prepareGameTeamUpdate()->execute($gameTeam);
            $this->results->countGameTeamsUpdate++;
        }
        return $gameTeam;
    }
    protected function updateGameOfficial($gameOfficial,$role,$name)
    {
        // See if official needs updating
        $needUpdate = false;
        
        if ($gameOfficial['role'] != $role) { $gameOfficial['role'] = $role; $needUpdate = true; }
        
        if ($gameOfficial['name'] != $name)     
        { 
            $gameOfficial['name']  = $name;
            $gameOfficial['assignState'] = null;
            $needUpdate = true; 
        }
        if ($needUpdate)
        {
            $this->helper->prepareGameOfficialUpdate()->execute($gameOfficial);
            $this->results->countGameOfficialsUpdate++;
        }
        return $gameOfficial;
    }
    /* ==================================================
     * Ong game with normalized data
     */
    protected function processGame($row)
    {
        // Sanity check
        $num = $row['num'];
        if (!$num) return;
        
        // Get the project and level
        $projectKey = $this->processProject($row);
        $levelKey   = $this->processLevel  ($row);
       
        // Process the project teams
        foreach($row['teams'] as $team)
        {
            $this->processProjectTeam($projectKey,$levelKey,$team['name']);
        }
        
        // Query game
        $game = $this->queryGame($projectKey,$num);
        if (!$game) return $this->insertGame($projectKey,$num,$levelKey,$row);

        // See if game needs updating
        $this->updateGame($levelKey,$game,$row);
        
        // Update Game Teams
        $gameId = $game['id'];
        $gameTeams = $this->queryGameTeams($gameId);
        
        // Make make bidirectional like officials though number of teams and slots should never change
        foreach($gameTeams as $gameTeam)
        {
            $slot = $gameTeam['slot'];
            $this->updateGameTeam($levelKey,$gameTeam,$row['teams'][$slot]);
        }

        // Update game officials - tricky
        $gameOfficials = $this->queryGameOfficials($gameId);
        $rowOfficials  = $row['officials'];
        
        foreach($rowOfficials as $rowOfficial)
        {
            $slot = $rowOfficial['slot'];
            if (!isset($gameOfficials[$slot]))
            {
                // Insert new recprd
                $rowOfficial['gameId'] = $gameId;
                $this->helper->prepareGameOfficialInsert()->execute($rowOfficial);
                $this->results->countGameOfficialsInsert++;
            }
            else
            {
                $this->updateGameOfficial($gameOfficials[$slot],$rowOfficial['role'],$rowOfficial['name']);
            }
        }
        // Delete any existing records
        foreach($gameOfficials as $gameOfficial)
        {
            $slot = $gameOfficial['slot'];
            if (!isset($rowOfficials[$slot]))
            {
                $this->helper->prepareGameOfficialDelete()->execute(array('id' => $gameOfficial['id']));
                $this->results->countGameOfficialsDelete++;
            }
        }
        return;
    }
    /* =================================================================
     * Misc
     */
    protected function processGameTeamName($name)
    {
        return $name ? $name : 'TBD';
    }
    protected function processGameTeamScore($score,$gameReportStatus)
    {
        // No report means no score
        if (!$gameReportStatus) return null;
        
        $score = (integer)$score;
        
        // PHP stripping away 0 strings
        return $score ? $score : 0;
    }
    /* ===============================================================
     * Does some cleanup and transformations
     */
    protected function processRow($row)
    {
        // Normalize report status
        $gameReportStatus = $row['gameReportStatus'];
        if ($gameReportStatus == 'No Report') $gameReportStatus = null;
        
        $rowx = array
        (
            'sport'  => $this->sport,
            'season' => $this->season,
            'domain' => $this->domain,
            
            'domainSub' => $row['domainSub'],
            'level'     => $row['level'],
            
            'num'   => (int)$row['num'],
            
            'fieldName' => $row['site'],
            'venueName' => null,
            
            'status' => $row['status'],
            
            'dtBeg' => str_replace('T',' ',$row['dtBeg']),
            'dtEnd' => str_replace('T',' ',$row['dtEnd']),
            
            'gameReportStatus' => $gameReportStatus,
        );
        if ($row['siteSub'])
        {
            // Merrimack, MM05
            $rowx['fieldName'] = sprintf('%s, %s',$row['site'],$row['siteSub']);
            $rowx['venueName'] = $row['site'];
        }
        /* ======================================================
         * Pull out teams
         */
        $homeTeam = array
        (
            'slot'  => 1,
            'role'  => 'Home',
            'name'  => $this->processGameTeamName ($row['homeTeamName']),
            'score' => $this->processGameTeamScore($row['homeTeamScore'],$gameReportStatus),
        );
        $awayTeam = array
        (
            'slot'  => 2,
            'role'  => 'Away',
            'name'  => $this->processGameTeamName ($row['awayTeamName']),
            'score' => $this->processGameTeamScore($row['awayTeamScore'],$gameReportStatus),
        );
        $rowx['teams'] = array(1 => $homeTeam, 2 => $awayTeam);
        
        /* =========================================================
         * Pull out officials
         */
        $noRoles = array(
            1 => 'No First Position',
            2 => 'No Second Position',
            3 => 'No Third Position',
            4 => 'No Fourth Position',
            5 => 'No Fifth Position'
        );
        $officials = array();
        for($slot = 1; $slot <= 5; $slot++)
        {
            $roleIndex = 'officialRole' . $slot;
            $nameIndex = 'officialName' . $slot;
            
            if ($row[$roleIndex] != $noRoles[$slot])
            {
                $name = $row[$nameIndex] != 'Empty' ? $row[$nameIndex] : null;
                
                $officials[$slot] = array
                (
                    'slot'  => $slot,
                    'role'  => $row[$roleIndex],
                    'name'  => $name,
                    'assignState' => null,
                );
           }
        }
        $rowx['officials'] = $officials;
        
        return $this->processGame($rowx);
    }
    /* ===============================================================
     * Starts everything off
     */
    public function process($params)
    {
        // These never change
        $this->sport  = $params['sport'];
        $this->season = $params['season'];
        $this->domain = $params['domain'];
 
        // Setup results collector
        $results = $this->results;
        $results->filepath = $params['filepath'];
        $results->basename = $params['basename'];
        $results->countGamesTotal = 0;
        
        // Must be a report file
        $reader = new \XMLReader();
        $reader->open($params['filepath'],null,LIBXML_COMPACT | LIBXML_NOWARNING);
        
       // Position to Report node
        if (!$reader->next('Report')) 
        {
            $results->message = '*** Not a Report file';
            $reader->close();
            return $results;
        }
        // Verify report type
        $reportType = $reader->getAttribute('Name');
        if ($reportType != 'Games with Slots')
        {
            $results->message = '*** Unexpected report type: ' . $reportType;
            $reader->close();
            return $results;
        }
        // Kind of screw but oh well
        while ($reader->read() && $reader->name !== 'Detail');
        
        // Loop
        $this->helper->beginTransaction();
        while($reader->name == 'Detail')
        {
            $row = array();
          //$row['sport']  = $params['sport'];
          //$row['domain'] = $params['domain'];
          //$row['season'] = $params['season'];
        
            foreach($this->map as $key => $attr)
            {
                $row[$key] = trim($reader->getAttribute($attr));
            }
            $results->countGamesTotal++;
            
            $this->processRow($row);

            // On to the next one
            $reader->next('Detail');
        }
        $this->helper->commit();
        
        // Done
        $reader->close();
        return $results;
    }
    /* ===========================================================
     * xmlReader does not have a simple getAttributes command
     * Could iterate over it but a map is prettly as fast
     */
    protected $map = array
    (
        'num'           => 'GameID',
        'dtBeg'         => 'From_Date',    // 2013-03-08T16:30:00
        'dtEnd'         => 'To_Date',
        'domainSub'     => 'Sport',        // AHSAA
        'level'         => 'Level',        // MS-B
        'site'          => 'Site',
        'siteSub'       => 'Subsite',
        'homeTeamName'  => 'Home_Team',
        'homeTeamScore' => 'Home_Score',
        'awayTeamName'  => 'Away_Team',
        'awayTeamScore' => 'Away_Score',
        
        'status'        => 'Status',
        
        'officialSlots' => 'Slots_Total',
        
        'officialRole1' => 'First_Position',  // Referee 
        'officialRole2' => 'Second_Position', // AR1 (or possibly dual?
        'officialRole3' => 'Third_Position',  // AR2
        'officialRole4' => 'Fourth_Position', // 'No Fourth Position'
        'officialRole5' => 'Fifth_Position',  // 'No Fifth Position' 
        
        'officialName1' => 'First_Official', 
        'officialName2' => 'Second_Official', 
        'officialName3' => 'Third_Official', 
        'officialName4' => 'Fourth_Official',  // 'Empty'
        'officialName5' => 'Fifth_Official',   // 'Empty'
        
        'billTo'        => 'BillTo_Name',
        'billAmount'    => 'Bill_Amount',     // 100.00
        'billFees'      => 'Total_Game_Fees', //  37.00 ?
        
        'gameNote'      => 'Game_Note',    // 'No Note'
        'gameNoteDate'  => 'Note_Date=',   //  Blank
        
        'gameReportComments' => 'Game_Report_Comments',
        'gameReportDateTime' => 'Report_Posted_Date',   // 1900-01-01T00:00:00
        'gameReportStatus'   => 'Report_Status',        // 'No Report'
        'gameReportOfficial' => 'Reporting_Official',
        
    );
}
/* =====================================
 * <Detail 
 * Note_Date="" Game_Note="No Note" 
 * Fifth_Official="Empty" Fifth_Position="No Fifth Position" 
 * Fourth_Official="Empty" Fourth_Position="No Fourth Position" 
 * Third_Official="Empty" Third_Position="No Third Position" 
 * Second_Official="Empty" Second_Position="No Second Position" 
 * First_Official="Tom Lawson" First_Position="Referee" 
 * Slots_Total="1" 
 * Reporting_Official="" Report_Status="No Report" Report_Posted_Date="1900-01-01T00:00:00" 
 * Game_Report_Comments="" 
 * Status="Normal" 
 * Total_Game_Fees="25.00" Bill_Amount="25.00" BillTo_Name="HISL" 
 * Away_Score="" Away_Team="3-4_SJS-3" 
 * Home_Score="" Home_Team="3-4_Rand_Blu" 
 * Subsite="" Site="Randolph Drake Campus" 
 * Level="3rd4th" Sport="HISL" 
 * To_Date="2013-09-17T17:35:00" 
 * From_Date="2013-09-17T16:45:00" 
 * Game_LinkID="1091" 
 * GameID="2068"
 * />
 */
?>
