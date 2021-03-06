<?php
namespace Cerad\Bundle\GameBundle\EntityRepository;

use Cerad\Bundle\CoreBundle\Doctrine\EntityRepository;

class GameRepository extends EntityRepository
{   
    public function createGame($params = null) { return $this->createEntity($params); }

    /* ==========================================================
     * Find stuff
     */
    public function findOneByProjectNum($projectKey,$num)
    {
        return $this->findOneBy(array('projectKey' => $projectKey, 'num' => $num));    
    }
    /* ========================================================
     * Generic schedule query
     * criteria is just an array
     */
    public function queryGameSchedule($criteria)
    {
        $nums        = $this->getArrayValue($criteria,'nums');
        $dates       = $this->getArrayValue($criteria,'dates');
        $levelKeys   = $this->getArrayValue($criteria,'levelKeys');
        $projectKeys = $this->getArrayValue($criteria,'projectKeys');
        
        $teamNames  = $this->getArrayValue($criteria,'teams');
        $fieldNames = $this->getArrayValue($criteria,'fields');
       
        /* =================================================
         * Dates are always so much fun
         */
        $date1 = $this->getScalerValue($criteria,'date1');
        $date2 = $this->getScalerValue($criteria,'date2');
        
        // Need more work here, both are on then select two dates OR op
        $date1On = $this->getScalerValue($criteria,'date1On');
        $date2On = $this->getScalerValue($criteria,'date2On');
        
        $date1Ignore = $this->getScalerValue($criteria,'date1Ignore');
        $date2Ignore = $this->getScalerValue($criteria,'date2Ignore');
        
        if ($date1On && !$date2On) $date2 = null;
        if ($date2On && !$date1On) $date1 = null;
        
        if ($date1Ignore) $date1 = null;
        if ($date2Ignore) $date2 = null;
        
        if (!$date1) $date1On = false;
        if (!$date2) $date2On = false;
        
        if ($date1 && $date2 && ($date1 > $date2))
        {
            $tmp = $date1; $date1 = $date2; $date2 = $tmp;
        }
        
        /* ===========================================
         * Game ID query
         */
        $qb = $this->createQueryBuilder('game');
     
        $qb->select('distinct game.id');
        $qb->leftJoin ('game.teams','gameTeam');
        
        if ($projectKeys)
        {
            $qb->andWhere('game.projectKey IN (:projectKeys)');
            $qb->setParameter('projectKeys',$projectKeys);
        }
        if ($dates)
        {
            $qb->andWhere('DATE(game.dtBeg) IN (:dates)');
            $qb->setParameter('dates',$dates);
        }
        if ($fieldNames)
        {
            $qb->andWhere('game.fieldName IN (:fieldNames)');
            $qb->setParameter('fieldNames',$fieldNames);
        }
        if ($levelKeys)
        {
            $qb->andWhere('gameTeam.levelKey IN (:levelKeys)');
            $qb->setParameter('levelKeys',$levelKeys);
        }
        /* ============================================
         * This is what makes me grab gamesIds first
         */
        if ($teamNames)
        {
            $qb->andWhere('gameTeam.name IN (:teamNames)');
            $qb->setParameter('teamNames',$teamNames);
        }
        
        if ($date1On and $date2On)
        {
           $qb->andWhere('((DATE(game.dtBeg) = :date1) OR (DATE(game.dtBeg) = :date2))');
           $qb->setParameter('date1',$date1);
           $qb->setParameter('date2',$date2);
           $date1 = null;
           $date2 = null;
        }
        if ($date1)
        {
            $op = $date1On ? '=' : '>=';
            $qb->andWhere('DATE(game.dtBeg) ' . $op . ' (:date1)');
            $qb->setParameter('date1',$date1);
        }
        if ($date2)
        {
            $op = $date2On ? '=' : '<=';
            $qb->andWhere('DATE(game.dtEnd) ' . $op . ' (:date2)');
            $qb->setParameter('date2',$date2);
        }
        if ($nums)
        {
            $qb->andWhere('game.num IN (:nums)');
            $qb->setParameter('nums',$criteria['nums']);
        }
      //echo $qb->getDql();
        $gameIds = $qb->getQuery()->getScalarResult();
   
        if (count($gameIds) < 1) return array();
        
        $ids = array();
        array_walk($gameIds, function($row) use (&$ids) { $ids[] = $row['id']; });
        
        // Game query
        $qbx = $this->createQueryBuilder('game');
        $qbx->addSelect('gameTeam,gameOfficial');
        $qbx->leftJoin ('game.teams',    'gameTeam');
        $qbx->leftJoin ('game.officials','gameOfficial');
        $qbx->andWhere ('game.id IN (:ids)');
        $qbx->setParameter('ids',$ids);
        
      // Sadly, this does not work, need to keep experimenting
      //$qbx->andWhere ('game.id IN (' . $qb->getDql() . ')');

        $qbx->addOrderBy('game.dtBeg');

        return $qbx->getQuery()->getResult();
    }
    /* ========================================================
     * Distinct list of field names for a set of projects
     */
    public function queryFieldChoices($criteria = array())
    {
        $projectKeys = $this->getArrayValue($criteria,'projectKeys');
        
        // Build query
        $qb = $this->createQueryBuilder('game');
        
        $qb->select('distinct game.fieldName');
        
        if ($projectKeys)
        {
            $qb->andWhere('game.projectKey IN (:projectKeys)');
            $qb->setParameter('projectKeys',$projectKeys);
        }
        $qb->addOrderBy('game.fieldName');
       
        $rows = $qb->getQuery()->getScalarResult();
        
        $choices = array();
        
        array_walk($rows, function($row) use (&$choices) 
        { 
            $choices[$row['fieldName']] = $row['fieldName']; 
        });
        return $choices;
    }
    /* ========================================================
     * Distinct list of team names for a set of projects and levels
     */
    public function queryTeamChoices($criteria = array())
    {
        $levelKeys   = $this->getArrayValue($criteria,'levelKeys');
        $projectKeys = $this->getArrayValue($criteria,'projectKeys');

        // Build query
        $qb = $this->createQueryBuilder('game');
        
        $qb->select('distinct gameTeam.name');
        
        $qb->leftJoin('game.teams','gameTeam');
        
        if ($projectKeys)
        {
            $qb->andWhere('game.projectKey IN (:projectKeys)');
            $qb->setParameter('projectKeys',$projectKeys);
        }
        if ($levelKeys)
        {
            $qb->andWhere('gameTeam.levelKey IN (:levelKeys)');
            $qb->setParameter('levelKeys',$levelKeys);
        }
        $qb->addOrderBy('gameTeam.name');
       
        $rows = $qb->getQuery()->getScalarResult();
        
        $choices = array();
        
        array_walk($rows, function($row) use (&$choices) { $choices[$row['name']] = $row['name']; });
        
        return $choices;
    }
}
?>
