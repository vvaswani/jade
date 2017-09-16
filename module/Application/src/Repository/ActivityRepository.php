<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\Types\Type;
use Application\Entity\Activity;

class ActivityRepository extends EntityRepository
{

    public function getRecentActivities($resultOffset = 0, $resultBatchSize = 10, $recentIntervalHours = 72)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a')
            ->from('Application\Entity\Activity', 'a')
            ->where('a.creationTime >= ?1')
            ->add('orderBy', 'a.id DESC')
            ->setFirstResult($resultOffset)
            ->setMaxResults($resultBatchSize)
            ->setParameter(1, new \DateTime('-' . $recentIntervalHours . ' hour'));
        $query = $qb->getQuery();
        return $query->getResult();        
    }

    public function getRecentActivitiesByJob($jid, $maxResults = 10, $recentIntervalHours = 72)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a')
            ->from('Application\Entity\Activity', 'a')
            ->where('a.creationTime >= ?1')
            ->andWhere('a.entityType = ?2 AND a.entityId = ?3')
            ->orWhere('a.associatedEntityType = ?2 OR a.associatedEntityId = ?3')
            ->add('orderBy', 'a.id DESC')
            ->setMaxResults($maxResults)
            ->setParameter(1, new \DateTime('-' . $recentIntervalHours . ' hour'))
            ->setParameter(2, Activity::ENTITY_TYPE_JOB)
            ->setParameter(3, $jid);
        $query = $qb->getQuery();
        return $query->getResult();        
    }
}