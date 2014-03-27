<?php

namespace Cerad\Bundle\GameBundle\Entity;

/* ==============================================
 * Unique teams within a project
 * Basically a physical team
 * 
 * 
 */
class ProjectTeam
{
    const RoleFake     = 'Fake';
    const RolePhysical = 'Physical';
    const RoleSchedule = 'Schedule';

    protected $id;
    protected $role;          // Physical, Pool, Placeholder etc
    protected $name;          // name and levelKey should be uniqe withing a project
    protected $groupKey;      // for section matches?
    protected $levelKey;
    protected $projectKey;
    protected $link;          // Same team could be in multiple projects
    protected $status;
    
    public function getId()         { return $this->id;         }
    public function getRole()       { return $this->role;       }
    public function getName()       { return $this->name;       }
    public function getStatus()     { return $this->status;     }
    public function getGroupKey()   { return $this->groupKey;   }
    public function getLevelKey()   { return $this->levelKey;   }
    public function getProjectKey() { return $this->projectKey; }
    
    public function setRole      ($value) { $this->role       = $value; }
    public function setName      ($value) { $this->name       = $value; }
    public function setStatus    ($value) { $this->status     = $value; }
    public function setGroupKey  ($value) { $this->groupKey   = $value; }
    public function setLevelKey  ($value) { $this->levelKey   = $value; }
    public function setProjectKey($value) { $this->projectKey = $value; }
}
?>
