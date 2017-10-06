<?php
namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\Types\Type;
use Application\Entity\Job;
use Application\Entity\Job\Log;
use Application\Service\AuthorizationService;

class JobRepository extends EntityRepository
{
	public function getAuthorizedJobs($identity, $status = Job::STATUS_OPEN, $controller = 'job', $action = 'view')
	{
        $qb = $this->getEntityManager()->createQueryBuilder();
        $as = new AuthorizationService;
        $qb->select('j')
            ->from('Application\Entity\Job', 'j')
            ->where('j.status = ?1')
            ->add('orderBy', 'j.creationTime DESC')
            ->setParameter(1, $status);
        $query = $qb->getQuery();
        $results = $query->getResult();
        $jobs = [];
        foreach ($results as $job) {
        	if ($as->isAuthorized($identity, $controller, $action, $job) === true) {
            	$jobs[] = $job;
        	}
        }
        return $jobs;
	}

	public function getJobLogs($id, $from, $to)
	{
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('l')
            ->from('Application\Entity\Job\Log', 'l')
            ->where('l.job = ?1')
            ->add('orderBy', 'l.date DESC')
            ->setParameter(1, $id);
        if ($from) {
            $qb->andWhere('l.date >= ?2')
            ->setParameter(2, $from);
        }
        if ($to) {
            $qb->andWhere('l.date <= ?3')
            ->setParameter(3, $to);
        }
        $query = $qb->getQuery();
        return $query->getResult();
	}
}
