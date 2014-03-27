<?php
namespace Cerad\Bundle\GameBundle\Schedule\Import;

use Symfony\Component\Stopwatch\Stopwatch;

class ArbiterImportResults
{
    public $message;
    public $filepath;
    public $basename;
    
    public $countGamesTotal  = 0;
    public $countGamesInsert = 0;
    public $countGamesUpdate = 0;
    
    public $countGameTeamsInsert = 0;
    public $countGameTeamsUpdate = 0;
    
    public $countGameOfficialsInsert = 0;
    public $countGameOfficialsUpdate = 0;
    public $countGameOfficialsDelete = 0;
    
    public $countProjectTeamsInsert = 0;
    
    public $duration;
    public $memory;
    
    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start('import');
    }
    public function __toString()
    {
        // Should probably not be here
        $event = $this->stopwatch->stop('import');
        $this->duration = $event->getDuration();
        $this->memory = $event->getMemory();

        return  sprintf(
            "Arbiter Import %s %s\n" . 
            "Games Tot %4d Insert: %d, Update: %d\n" .
            "Game Teams     Insert: %d, Update: %d\n" .
            "Game Officials Insert: %d, Update: %d, Delete: %d\n" .
            "Project  Teams Insert: %d\n" .
            "Duration %.2f %.2fM\n",
            $this->message,
            $this->basename,
                
            $this->countGamesTotal,
            $this->countGamesInsert,
            $this->countGamesUpdate,
                
            $this->countGameTeamsInsert,
            $this->countGameTeamsUpdate,
                
            $this->countGameOfficialsInsert,
            $this->countGameOfficialsUpdate,
            $this->countGameOfficialsDelete,
                
            $this->countProjectTeamsInsert,
            
            $this->duration / 1000.,
            $this->memory   / 1000000.
        );
    }
}
?>
