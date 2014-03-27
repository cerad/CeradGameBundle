<?php
namespace Cerad\Bundle\GameBundle\EntityRepository;

//  Cerad\Bundle\GameBundle\Entity\Level as LevelEntity;

use Cerad\Library\Doctrine\AbstractRepository;

class LevelRepository extends AbstractRepository
{   
    public function createLevel($params = null) { return $this->createEntity($params); }
    
    /* ================================================
     * Return a list of level ids
     */
    public function queryLevelKeys($criteria = array())
    {
        $names      = $this->getArrayValue($criteria,'levels'    );
        $sports     = $this->getArrayValue($criteria,'sports'    );
        $domains    = $this->getArrayValue($criteria,'domains'   );
        $domainSubs = $this->getArrayValue($criteria,'domainSubs');
        
        // Build query
        $qb = $this->createQueryBuilder('level');

        $qb->select('distinct level.key');
        
        if ($names)
        {
            $qb->andWhere('level.name IN (:names)');
            $qb->setParameter('names',$names);
        }
        if ($sports)
        {
            $qb->andWhere('level.sport IN (:sports)');
            $qb->setParameter('sports',$sports);
        }
        if ($domains)
        {
            $qb->andWhere('level.domain IN (:domains)');
            $qb->setParameter('domains',$domains);
        }
        if ($domainSubs)
        {
            $qb->andWhere('level.domainSub IN (:domainSubs)');
            $qb->setParameter('domainSubs',$domainSubs);
        }
      //echo $qb->getDql();
        
        $rows = $qb->getQuery()->getScalarResult();
        $keys = array();
        array_walk($rows, function($row) use (&$keys) 
        { 
            $keys[] = $row['key']; 
        });
        return $keys;
    }
    /* ==================================================
     * For level pick list
     * Should level have a sport?  Varsity etc
     */
    public function queryLevelChoices($criteria = array())
    {
        $sports     = $this->getArrayValue($criteria,'sports');
        $domains    = $this->getArrayValue($criteria,'domains');
        $domainSubs = $this->getArrayValue($criteria,'domainSubs');
        
        // Build query
        $qb = $this->createQueryBuilder('level');

        $qb->select('distinct level.name');
        
        if ($sports)
        {
            $qb->andWhere('level.sport IN (:sports)');
            $qb->setParameter('sports',$sports);
        }
        if ($domains)
        {
            $qb->andWhere('level.domain IN (:domains)');
            $qb->setParameter('domains',$domains);
        }
        if ($domainSubs)
        {
            $qb->andWhere('level.domainSub IN (:domainSubs)');
            $qb->setParameter('domainSubs',$domainSubs);
        }
        $qb->addOrderBy('level.name');
        
        $rows = $qb->getQuery()->getScalarResult();
        $choices = array();
        array_walk($rows, function($row) use (&$choices) 
        { 
            $choices[$row['name']] = $row['name']; 
        });
        return $choices;
    }
}
?>
