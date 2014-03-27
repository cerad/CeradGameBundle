<?php
namespace Cerad\Bundle\GameBundle\Schedule\Import;

/* =========================================================
 * Calling it helper for lack of a better word
 * 
 * Move the assorted prepared statements to here
 * 
 * Sort of like a repo but not really
 */
class ArbiterGamesImportHelper
{
    protected $conn;
    protected $prepared = array();

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function commit          () { return $this->conn->commit();           }
    public function rollBack        () { return $this->conn->rollBack();         }
    public function lastInsertId    () { return $this->conn->lastInsertId();     }
    public function beginTransaction() { return $this->conn->beginTransaction(); }
    
    /* ===============================================================
     * Project Code
     */
    public function prepareProjectSelect()
    {
        $key = 'projectSelect';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
        
        $sql = <<<EOT
SELECT keyx FROM projects WHERE keyx = :key;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareProjectInsert()
    {
        $key = 'projectInsert';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];

        $sql = <<<EOT
INSERT INTO projects
       ( keyx, season, sport, domain, domainSub, status)
VALUES (:key, :season,:sport,:domain,:domainSub, 'Active')
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    /* ===============================================================
     * Level Code
     */
    public function prepareLevelSelect()
    {
        $key = 'levelSelect';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];

        $sql = <<<EOT
SELECT id FROM levels WHERE id = :key;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareLevelInsert()
    {
        $key = 'levelInsert';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
        
        $sql = <<<EOT
INSERT INTO levels
       ( id,  name, sport, domain, domainSub, status)
VALUES (:key,:name,:sport,:domain,:domainSub, 'Active')
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    /* ==================================================
     * Game Select,Insert,Update
     * Select matches Update
     */
    public function prepareGameSelect()
    {
        $key = 'gameSelect';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
        
        $sql = <<<EOT
SELECT
    game.id        AS id,
    game.levelKey  AS levelKey,
    game.fieldName AS fieldName,
    game.venueName AS venueName,
    game.dtBeg     AS dtBeg,
    game.dtEnd     AS dtEnd,
    game.status    AS status
FROM  games AS game
WHERE game.projectKey = :projectKey AND game.num = :num;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareGameUpdate()
    {
        $key = 'gameUpdate';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
        
        $sql = <<<EOT
UPDATE games SET
    fieldName = :fieldName,
    venueName = :venueName,
    levelKey  = :levelKey,
    dtBeg     = :dtBeg,
    dtEnd     = :dtEnd,
    status    = :status
WHERE id = :id
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareGameInsert()
    {
        $key = 'gameInsert';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
        
        $sql = <<<EOT
INSERT INTO games
       ( projectKey, num, role,  levelKey, fieldName, venueName, dtBeg, dtEnd, status)
VALUES (:projectKey,:num,'Game',:levelKey,:fieldName,:venueName,:dtBeg,:dtEnd,:status)
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    /* ==================================================
     * Game Teams
     */
    public function prepareGameTeamsSelect()
    {
        $key = 'gameTeamsSelect';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
        
        $sql = <<<EOT
SELECT
    gameTeam.id       AS id,
    gameTeam.slot     AS slot,
    gameTeam.role     AS role,
    gameTeam.levelKey AS levelKey,
    gameTeam.name     AS name,
    gameTeam.score    AS score
FROM  game_teams AS gameTeam
WHERE gameTeam.gameId = :gameId 
ORDER BY gameTeam.slot;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareGameTeamUpdate()
    {
        $key = 'gameTeamUpdate';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
        
        $sql = <<<EOT
UPDATE game_teams SET
    slot     = :slot,
    role     = :role,
    levelKey = :levelKey,
    name     = :name,
    score    = :score
WHERE id = :id
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareGameTeamInsert()
    {
        $key = 'gameTeamInsert';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
        
        $sql = <<<EOT
INSERT INTO game_teams
       ( gameId, slot, role, levelKey, name, score, status)
VALUES (:gameId,:slot,:role,:levelKey,:name,:score,'Active')
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    /* ==================================================
     * Project Teams
     */
    public function prepareProjectTeamSelect()
    {
        $key = 'projectTeamSelect';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
                
        $sql = <<<EOT
SELECT
    team.id   AS id,
    team.name AS name
FROM  
    project_teams AS team
WHERE 
    team.projectKey = :projectKey AND
    team.levelKey   = :levelKey   AND
    team.name       = :name
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareProjectTeamInsert()
    {
        $key = 'projectTeamInsert';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
                        
        $sql = <<<EOT
INSERT INTO project_teams
       ( projectKey, levelKey, role,      name)
VALUES (:projectKey,:levelKey,'Physical',:name)
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    /* ==================================================
     * Game Officials
     */
    public function prepareGameOfficialsSelect()
    {
        $key = 'gameOfficialsSelect';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
                
        $sql = <<<EOT
SELECT
    official.id             AS id,
    official.slot           AS slot,
    official.role           AS role,
    official.assignState    AS assignState,
    official.personNameFull AS name
FROM  
    game_officials AS official
WHERE 
    official.gameId = :gameId
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareGameOfficialUpdate()
    {
        $key = 'gameOfficialsUpdate';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
                
        $sql = <<<EOT
UPDATE game_officials SET
    slot           = :slot,
    role           = :role,
    assignState    = :assignState,
    personNameFull = :name
WHERE id = :id
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareGameOfficialInsert()
    {
        $key = 'gameOfficialsInsert';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
                
        $sql = <<<EOT
INSERT INTO game_officials
      ( gameId, slot, role, assignState, personNameFull)
VALUES(:gameId,:slot,:role,:assignState,:name)
;
EOT;
        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
    public function prepareGameOfficialDelete()
    {
        $key = 'gameOfficialDelete';
        
        if (isset($this->prepared[$key])) return $this->prepared[$key];
                        
        $sql = 'DELETE FROM game_officials WHERE id = :id;';

        return $this->prepared[$key] = $this->conn->prepare($sql);
    }
}
?>
