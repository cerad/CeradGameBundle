<?php
namespace Cerad\Bundle\GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/* ==============================================
 * Each game has a project and a level
 * game.num is unique within project
 */
class Game
{
    const RoleGame      = 'Game';
    const RolePractice  = 'Practice';
    const RoleScrimmage = 'Scrimmage';
    const RoleJamboree  = 'Jamboree';
    const RoleReserve   = 'Reserve';

    protected $id;
    
    protected $num;   // Unique within project
    protected $role = self::RoleGame;
    
    protected $groupKey;
    protected $groupType; // PP QF SF FM CM SM
    
    protected $link;   // Maybe to link crews?
    
    protected $dtBeg; // DateTime begin
    protected $dtEnd; // DateTime end
    
    protected $levelKey;
    protected $projectKey;
    
  //protected $field;     // Name for now, needs to be a link to project fields or game fields
    protected $fieldName; // This is unique 
    protected $venueName; // Very handy to have
    
    protected $status;
    
    protected $teams;
    protected $officials;
    
    public function getId()      { return $this->id;      }
    public function getNum()     { return $this->num;     }
    public function getRole()    { return $this->role;    }
    public function getDtBeg()   { return $this->dtBeg;   }
    public function getDtEnd()   { return $this->dtEnd;   }
    public function getStatus()  { return $this->status;  }
    
  //public function getField()      { return $this->field;      }
    public function getFieldName()  { return $this->fieldName;  }
    public function getVenueName()  { return $this->venueName;  }
    
    public function getLevelKey()   { return $this->levelKey;   }
    public function getProjectKey() { return $this->projectKey; }
    public function getGroupKey()   { return $this->groupKey;   }
    public function getGroupType()  { return $this->groupType;  }
    
    public function setNum      ($value) { $this->num    = $value; }
    public function setRole     ($value) { $this->role   = $value; }
    public function setDtBeg    ($value) { $this->dtBeg  = $value; }
    public function setDtEnd    ($value) { $this->dtEnd  = $value; }
    public function setStatus   ($value) { $this->status = $value; }
    
  //public function setField    ($value) { $this->field     = $value; }
    public function setFieldName($value) { $this->fieldName = $value; }
    public function setVenueName($value) { $this->venueName = $value; }
    
    public function setLevelKey  ($value) { $this->levelKey   = $value; }
    public function setProjectKey($value) { $this->projectKey = $value; }
    public function setGroupKey  ($value) { $this->groupKey   = $value; }
    public function setGroupType ($value) { $this->groupType  = $value; }
    
    public function __construct()
    {
        $this->teams   = new ArrayCollection();
        $this->persons = new ArrayCollection();  // Rename to officials some time
    }
    /* =======================================
     * Team stuff
     */
   public function createGameTeam($params = null) { return new GameTeam($params); }
   
   public function getTeams($sort = true) 
    { 
        if (!$sort) return $this->teams;
        
        $items = $this->teams->toArray();
        
        ksort ($items);
        return $items; 
    }
    public function addTeam($team)
    {
        $this->teams[$team->getSlot()] = $team;
        
        $team->setGame($this);
        
      //$this->onPropertyChanged('teams');
    }
    public function getTeamForSlot($slot,$autoCreate = true)
    {
        if (isset($this->teams[$slot])) return $this->teams[$slot];
        
        if (!$autoCreate) return null;
        
        $gameTeam = $this->createGameTeam();
        $gameTeam->setSlot($slot);
        $role = $gameTeam->getRoleForSlot($slot);
        $gameTeam->setRole($role);
        
        $this->addTeam($gameTeam);
        return $gameTeam;
    }
    public function getHomeTeam($autoCreate = true) { return $this->getTeamForSlot(GameTeam::SlotHome,$autoCreate); }
    public function getAwayTeam($autoCreate = true) { return $this->getTeamForSlot(GameTeam::SlotAway,$autoCreate); }
    
    /* =======================================
     * Person stuff
     */
    public function createGameOfficial($params = null) { return new GameOfficial($params); }
    
    public function getOfficials($sort = true) 
    { 
        if (!$sort) return $this->officials;
        
        $items = $this->officials->toArray();
        
        ksort ($items);
        return $items; 
    }
    public function addOfficial($official)
    {
        $this->officials[$official->getSlot()] = $official;
        
        $official->setGame($this);
    }
    public function getOfficialForSlot($slot)
    {
        if (isset($this->officials[$slot])) return $this->officials[$slot];
        
        return null;
    }
    /* =========================================
     * Debugging
     * TODO: Fix Up
     */
    public function __toString()
    {
        die('game.__toString()');
        
        ob_start();

        echo sprintf("Game %-6s %-4s %6s %-8s %s   %-8s %-10s %s\n",
            $this->status,
            $this->role,
            $this->num,
            $this->projectKey,
            $this->dtBeg->format('d M Y H:i:s A'),
            $this->level->getDomainSub(),
            $this->level->getName(),
            $this->field->getName()
        );
        foreach($this->teams as $team)
        {
            echo $team . "\n";
        }
        return ob_get_clean();
    }
}
?>
