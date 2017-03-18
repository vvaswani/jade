<?php
namespace Application\Service;

use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use Application\Entity\Activity;
use Application\Entity\User;

class ActivityManagerService
{
    private $em;

    public function __construct(EntityManager $em, AuthenticationService $as)
    {
        $this->em = $em;
        $this->as = $as;
    } 

    public function flush($queue) 
    {
        if (count($queue)) {
            foreach ($queue as $item) {
                $this->log($item[0], $item[1], $item[2], $item[3], $item[4]);
            }
        }
    }

    /**
    * logs an activity
    *
    * @param  string    $operation          a constant indicating the operation type
    * @param  string    $ts                 the operation timestamp
    * @param  Entity    $entity             the entity on which the operation is being performed
    * @param  Entity    $associatedEntity   the associated entity (for operations involving associations only)
    * @param  array     $data               operation-related data (changes performed for updates, limited entity-specific data for associations)
    * @access private
    */
    private function log($operation, $ts, $entity, $associatedEntity = null, $data = null) 
    {
        $activity = new Activity();
        $user = $this->as->getIdentity();
        $activity->setCreated($ts);
        $activity->setOperation($operation);            
        if (is_null($user) && $operation == Activity::OPERATION_LOGOUT) {
            $user = $this->em->getRepository(User::class)->find($entity->getId());
        }
        $activity->setUser($user);
        $activity->setEntityId($entity->getId());
        $entityClassSegments = explode('\\', get_class($entity));     
        $entityClass = array_pop($entityClassSegments);
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