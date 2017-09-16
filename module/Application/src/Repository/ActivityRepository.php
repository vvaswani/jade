<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\Types\Type;

class ActivityRepository extends EntityRepository
{

    public function getRecentActivities($resultOffset = 0, $resultBatchSize = 10, $recentIntervalHours = 72)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a')
            ->from('Application\Entity\Activity', 'a')
            ->where('a.creationTime >= ?1')
            ->add('orderBy', 'a.creationTime DESC')
            ->setFirstResult($resultOffset)
            ->setMaxResults($resultBatchSize)
            ->setParameter(1, new \DateTime('-' . $recentIntervalHours . ' hour'));
        $query = $qb->getQuery();
        return $query->getResult();        
    }

}