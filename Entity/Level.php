<?php
namespace Cerad\Bundle\GameBundle\Entity;

class Level
{
    protected $key;
    
    protected $name;
    protected $sport;
    protected $domain;
    protected $domainSub;
    
    protected $age;
    protected $gender;
    protected $program; // Core/Extra/Regional/Premier etc
    
    protected $status;
    
    protected $link;   // Future, allow linking the same level across multiple domains
    
    public function getKey()       { return $this->key;    }
    public function getName()      { return $this->name;   }
    public function getLink()      { return $this->link;   }
    public function getStatus()    { return $this->status; }
    
    public function getSport()     { return $this->sport;     }
    public function getDomain()    { return $this->domain;    }
    public function getDomainSub() { return $this->domainSub; }
    
    public function getAge()     { return $this->age;     }
    public function getGender()  { return $this->gender;  }
    public function getProgram() { return $this->program; }
    
    public function setKey      ($value) { $this->key    = $value; }
    public function setName     ($value) { $this->name   = $value; }
    public function setLink     ($value) { $this->link   = $value; }
    public function setStatus   ($value) { $this->status = $value; }
    
    public function setSport    ($value) { $this->sport     = $value; }
    public function setDomain   ($value) { $this->domain    = $value; }
    public function setDomainSub($value) { $this->domainSub = $value; }
    
    public function setAge      ($value) { $this->age     = $value; }
    public function setGender   ($value) { $this->gender  = $value; }
    public function setProgram  ($value) { $this->program = $value; }
    
}
?>
