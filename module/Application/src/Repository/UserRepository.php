<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\Types\Type;
use Application\Entity\Activity;
use Application\Entity\Permission;
use Application\Entity\User;

class UserRepository extends EntityRepository
{

    public function getPotentialCollaboratorsByJob($jid)
    {
        $in  = $this->getEntityManager()->createQueryBuilder();
        $in->select('IDENTITY(p.user)')
           ->from('Application\Entity\Job\Permission', 'p')
           ->where('p.entity = ?1');
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u')
           ->from('Application\Entity\User', 'u')
           ->where($qb->expr()->notIn('u.id', $in->getDQL()))
           ->andWhere('u.status = ?2')
           ->andWhere('u.role != ?3')
           ->setParameter(1, $jid)
           ->setParameter(2, User::STATUS_ACTIVE)
           ->setParameter(3, User::ROLE_ADMINISTRATOR);
        $query = $qb->getQuery();
        return $query->getResult();
    }

}