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
 */
class ArbiterGamesWithSlotsImport
{
    protected $results;
    
    protected $sport;
    protected $domain;
    protected $season;
    protected $nums;
    
    protected $gameRepo;
    protected $levelRepo;
    protected $projectRepo;
    
    public function __construct($projectRepo,$levelRepo,$gameRepo)
    {
        $this->results = new ArbiterImportResults();
        
        $this->gameRepo    = $gameRepo;
        $this->levelRepo   = $levelRepo;
        $this->projectRepo = $projectRepo;
    }
    /* =========================================================
     * Locates existing project or creates a new one
     */
    protected function processProject($row)
    {
        $projectRepo = $this->projectRepo;
        
        $params = array($row['domain'],$row['sport'],$row['domainSub'],$row['season']);
        
        $projectId = $this->projectRepo->hash($params);
        
        $projectx = $projectRepo->find($projectId);
        if ($projectx) return $projectx;
        
        // New project
        $project = $projectRepo->createProject();
        $project->setId       ($projectId);
        $project->setSport    ($row['sport']);
        $project->setSeason   ($row['season']);
        $project->setDomain   ($row['domain']);
        $project->setDomainSub($row['domainSub']);
        
        $projectRepo->save($project);
        $projectRepo->commit();
        
        return $project;
    }
    /* =========================================================
     * Locates existing level or creates a new one
     */
    protected function processLevel($row)
    {
        $levelRepo = $this->levelRepo;
        
        $params = array($row['domain'],$row['sport'],$row['domainSub'],$row['level']);
        
        $levelId = $this->levelRepo->hash($params);
        
        $levelx = $levelRepo->find($levelId);
        if ($levelx) return $levelx;
        
        // New Level
        $level = $levelRepo->createLevel();
        $level->setId       ($levelId);
        $level->setName     ($row['level'    ]);
        $level->setSport    ($row['sport'    ]);
        $level->setDomain   ($row['domain'   ]);
        $level->setDomainSub($row['domainSub']);
        
        $levelRepo->save($level);
        $levelRepo->commit();
        
        return $level;
    }
    /* =================================================================
     * Game Team
     */
    protected function processGameTeam($game,$gameReportStatus,$team,$name,$score)
    {
        if (!$name) $name = 'TBD';
        
        $team->setName   ($name);
        $team->setLevelId($game->getLevelId());
        
        if ($gameReportStatus != 'No Report') 
        { 
            $score = (integer)$score;
            if (!$score) $score = 0; // PHP stripping away 0 strings
            $team->setScore((integer)$score); 
        }
        
        return $team;    
    }
    /* ==================================================
     * Handles one row at a time
     */
    protected function processRow($row)
    {
        // Process the game number
        $num = (integer)$row['num'];
        
        // Probably toss an exception
        if (!$num) return;
        
        // Really should not happem but does
        if (isset($this->nums[$num])) return;
        $this->nums[$num] = true;
        
        // Get the project and level
        $project = $this->processProject($row);
        $level   = $this->processLevel  ($row);
        
        // Get the game or create a new one
        $gameRepo = $this->gameRepo;
        $game = $gameRepo->findOneByProjectNum($project->getId(),$num);
        if (!$game)
        {
            $game = $gameRepo->createGame();
            $game->setProjectId($project->getId());
            $game->setNum($num);
            
            $this->results->countGamesInsert++;
        }
        $game->setField  ($row['site' ]);
        $game->setLevelId($level->getId());
        $game->setStatus ($row['status']);
        
        $dtBeg = \DateTime::createFromFormat('Y-m-d*H:i:s',$row['dtBeg']);
        $dtEnd = \DateTime::createFromFormat('Y-m-d*H:i:s',$row['dtEnd']);
       
        $game->setDtBeg($dtBeg);
        $game->setDtEnd($dtEnd);
        
        // Teams
        $this->processGameTeam($game,$row['gameReportStatus'],$game->getHomeTeam(),$row['homeTeamName'],$row['homeTeamScore']);
        $this->processGameTeam($game,$row['gameReportStatus'],$game->getAwayTeam(),$row['awayTeamName'],$row['awayTeamScore']);
        
        // And save
        $gameRepo->save($game);
        
        return;
        
        echo sprintf("Row %d %s %s %s %s\n",$num,$row['season'],$row['domain'],$row['domainSub'],$row['level']);
        
        return;
    }
    /* =================================================
     * Need to fool around with commits to prevent memory exhaustion
     */
    protected $commitCount;
    
    protected function commit()
    {
        if ($this->commitCount++ < 200) return;
        
        $this->gameRepo->commit();
        $this->commitCount = 0;
    }
    /* ===============================================================
     * Starts everything off
     */
    public function process($params)
    {
        // Save some
        $this->sport  = $params['sport'];
        $this->domain = $params['domain'];
        $this->season = $params['season'];
        $this->nums = array();
        
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
        
        $map = $this->map;
        
        // Loop
        while($reader->name == 'Detail')
        {
            $row = array();
            $row['sport']  = $params['sport'];
            $row['domain'] = $params['domain'];
            $row['season'] = $params['season'];
        
            foreach($this->map as $key => $attr)
            {
                $row[$key] = $reader->getAttribute($attr);
            }
            $results->countGamesTotal++;
            
            $this->processRow($row);

            $this->commit();
            
            // On to the next one
            $reader->next('Detail');
        }
        $this->gameRepo->commit();
        
        // Done
        $reader->close();
        return $results;
    }
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
