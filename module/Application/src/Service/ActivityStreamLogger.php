<?php
namespace Application\Service;

use Application\Entity\Activity;

class ActivityStreamLogger
{
    public function __construct($em)
    {
        $this->em = $em;
    }    

    public function log($operationType, $entityType, $entityId, $jobId = null, $data = null) 
    {
        $activity = new Activity();
        $activity->setCreated(new \DateTime("now"));
        $activity->setJobId($jobId);
        $activity->setUserId(1);
        $activity->setEntityId($entityId);
        $activity->setOperationType($operationType);
        $activity->setEntityType($entityType);
        $activity->setData(json_encode($data));
        $this->em->persist($activity); 
        $this->em->flush();
    }
}