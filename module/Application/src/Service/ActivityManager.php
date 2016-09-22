<?php
namespace Application\Service;

use Doctrine\ORM\EntityManager;
use Application\Entity\Activity;

class ActivityManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    } 

    public function flush($queue) 
    {
        if (count($queue)) {
            foreach ($queue as $item) {
                $this->log($item[0], $item[1], $item[2], $item[3], $item[4],  $item[5]);
            }
        }
    }

    private function log($user, $operation, $ts, $entity, $associatedEntity = null, $data = null) 
    {
        $activity = new Activity();
        $activity->setCreated($ts);
        $activity->setUserId($user->getId());
        $activity->setOperation($operation);
        $activity->setEntityId($entity->getId());
        $entityClass = array_pop(explode('\\', get_class($entity)));
        $activity->setEntityType(constant('Application\Entity\Activity::ENTITY_TYPE_' . strtoupper($entityClass)));
        if (!is_null($associatedEntity)) {
            $activity->setAssociatedEntityId($associatedEntity->getId());
            $associatedEntityClass = array_pop(explode('\\', get_class($associatedEntity)));
            $activity->setAssociatedEntityType(constant('Application\Entity\Activity::ENTITY_TYPE_' . strtoupper($associatedEntityClass)));
        }
        $activity->setData(json_encode($data));
        $this->em->persist($activity); 
        $this->em->flush();
    }
}