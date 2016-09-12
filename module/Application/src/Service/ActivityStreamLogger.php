<?php
namespace Application\Service;

use Application\Entity\Activity;
use Application\Entity\Job;

class ActivityStreamLogger
{
    public function __construct($em)
    {
        $this->em = $em;
    }    

    // TODO refactor function signature and remove $entity 
    // TODO if eventually used only for Job entities
    public function log($entityOperationType, $entity, $user, $job = null, $data = null) 
    {
        $activity = new Activity();
        $activity->setCreated(new \DateTime("now"));
        $activity->setOperationType($entityOperationType);
        $activity->setEntityId($entity->getId());
        $activity->setUserId($user->getId());
        (!is_null($job)) ? $activity->setJobId($job->getId()) : $activity->setJobId(null);
        // TODO add other entity types
        if ($entity instanceof Job) {
            $activity->setEntityType(Activity::ENTITY_TYPE_JOB);
        }
        $activity->setData(json_encode($data));
        $this->em->persist($activity); 
        $this->em->flush();
    }
}