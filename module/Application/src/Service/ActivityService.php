<?php
namespace Application\Service;

use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Application\Entity\Activity;
use Application\Entity\User;
use Application\Entity\Job;
use Application\Entity\Label;
use Application\Entity\Template;
use Application\Entity\Permission;
use Application\Entity\Job\File;
use Application\Entity\Job\Log;

class ActivityService
{
    private $em;

    private $as;

    private $queue;


    public function __construct(EntityManager $em, AuthenticationService $as)
    {
        $this->em = $em;
        $this->as = $as;
        $this->queue = array();
    }

    /**
    * @see http://stackoverflow.com/questions/15311083/whats-the-proper-use-of-unitofwork-getscheduledcollectiondeletions-in-doctr
    */
    public function onFlush(OnFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Job) {
                $this->queue[] = array(
                    Activity::OPERATION_CREATE,
                    new \DateTime("now"),
                    $entity,
                    null,
                    array('name' => $entity->getName())
                );
            }
            if ($entity instanceof Label) {
                $this->queue[] = array(
                    Activity::OPERATION_CREATE,
                    new \DateTime("now"),
                    $entity,
                    null,
                    array('name' => $entity->getName())
                );
            }
            if ($entity instanceof User) {
                $this->queue[] = array(
                    Activity::OPERATION_CREATE,
                    new \DateTime("now"),
                    $entity,
                    null,
                    array('name' => $entity->getName(), 'username' => $entity->getUsername())
                );
            }
            if ($entity instanceof File) {
                $this->queue[] = array(
                    Activity::OPERATION_CREATE,
                    new \DateTime("now"),
                    $entity,
                    $entity->getJob(),
                    array('filename' => $entity->getFilename())
                );
            }
            if ($entity instanceof Template) {
                $this->queue[] = array(
                    Activity::OPERATION_CREATE,
                    new \DateTime("now"),
                    $entity,
                    null,
                    array('name' => $entity->getName(), 'filename' => $entity->getFilename())
                );
            }
            if ($entity instanceof Permission) {
                $this->queue[] = array(
                    Activity::OPERATION_GRANT,
                    new \DateTime("now"),
                    $entity->getEntity(),
                    $entity->getUser(),
                    array(
                        'name' => $entity->getName(),
                        'user' => array('name' => $entity->getUser()->getName())
                    )
                );
            }
            if ($entity instanceof Log) {
                $this->queue[] = array(
                    Activity::OPERATION_CREATE,
                    new \DateTime("now"),
                    $entity,
                    $entity->getJob(),
                    array('description' => $entity->getDescription())
                );
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $diff = $uow->getEntityChangeSet($entity);
            if ($entity instanceof Job) {
                if (array_key_exists('customer', $diff)) {
                    if ($diff['customer'][0] != null) {
                        $diff['customer'][0] = $diff['customer'][0]->getName();
                    }

                    if ($diff['customer'][1] != null) {
                       $diff['customer'][1] = $diff['customer'][1]->getName();
                    }
                }
            }
            if ($entity instanceof Log) {
                if (array_key_exists('date', $diff)) {
                    if ($diff['date'][0]->format('Y-m-d') == $diff['date'][1]->format('Y-m-d')) {
                        unset($diff['date']);
                    } else {
                        $diff['date'][0] = $diff['date'][0]->format('Y-m-d');
                        $diff['date'][1] = $diff['date'][1]->format('Y-m-d');
                    }
                }
            }
            if ($entity instanceof Job || $entity instanceof Label || $entity instanceof User || $entity instanceof Template) {
                if (!empty($diff)) {
                    $this->queue[] = array(
                        Activity::OPERATION_UPDATE,
                        new \DateTime("now"),
                        $entity,
                        null,
                        $diff
                    );
                }
            }
            if ($entity instanceof File || $entity instanceof Log) {
                if (!empty($diff)) {
                    $this->queue[] = array(
                        Activity::OPERATION_UPDATE,
                        new \DateTime("now"),
                        $entity,
                        $entity->getJob(),
                        $diff
                    );
                }
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Job) {
                $this->queue[] = array(
                    Activity::OPERATION_DELETE,
                    new \DateTime("now"),
                    serialize($entity),
                    null,
                    array('name' => $entity->getName())
                );
            }
            if ($entity instanceof Label) {
                $this->queue[] = array(
                    Activity::OPERATION_DELETE,
                    new \DateTime("now"),
                    serialize($entity),
                    null,
                    array('name' => $entity->getName())
                );
            }
            if ($entity instanceof User) {
                $this->queue[] = array(
                    Activity::OPERATION_DELETE,
                    new \DateTime("now"),
                    serialize($entity),
                    null,
                    array('name' => $entity->getName(), 'username' => $entity->getUsername())
                );
            }
            if ($entity instanceof File) {
                $this->queue[] = array(
                    Activity::OPERATION_DELETE,
                    new \DateTime("now"),
                    serialize($entity),
                    serialize($entity->getJob()),
                    array('filename' => $entity->getFilename())
                );
            }
            if ($entity instanceof Template) {
                $this->queue[] = array(
                    Activity::OPERATION_DELETE,
                    new \DateTime("now"),
                    serialize($entity),
                    null,
                    array('name' => $entity->getName(), 'filename' => $entity->getFilename())
                );
            }
            if ($entity instanceof Permission) {
                $this->queue[] = array(
                    Activity::OPERATION_REVOKE,
                    new \DateTime("now"),
                    serialize($entity->getEntity()),
                    serialize($entity->getUser()),
                    array(
                        'name' => $entity->getName(),
                        'user' => array('name' => $entity->getUser()->getName())
                    )
                );
            }
            if ($entity instanceof Log) {
                $this->queue[] = array(
                    Activity::OPERATION_DELETE,
                    new \DateTime("now"),
                    serialize($entity),
                    serialize($entity->getJob()),
                    array('description' => $entity->getDescription())
                );
            }
        }

        foreach ($uow->getScheduledCollectionDeletions() as $col) {

        }

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            if ($collection->getTypeClass()->getName() == 'Application\Entity\Label')  {
                $entity = $collection->getOwner();
                $insertedEntities = $collection->getInsertDiff();
                if (count($insertedEntities)) {
                    foreach ($insertedEntities as $associatedEntity) {
                        $data = array(
                            'name' => $associatedEntity->getName(),
                            'colour' => $associatedEntity->getColour(),
                        );
                        $this->queue[] = array(
                            Activity::OPERATION_ASSOCIATE,
                            new \DateTime("now"),
                            $entity,
                            $associatedEntity,
                            $data
                        );
                    }
                }

                $deletedEntities = $collection->getDeleteDiff();
                if (count($deletedEntities)) {
                    foreach ($deletedEntities as $associatedEntity) {
                        $data = array(
                            'name' => $associatedEntity->getName(),
                            'colour' => $associatedEntity->getColour(),
                        );
                        $this->queue[] = array(
                            Activity::OPERATION_DISSOCIATE,
                            new \DateTime("now"),
                            $entity,
                            $associatedEntity,
                            $data
                        );
                    }
                }
            }
        }
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    public function flush()
    {
        $queue = $this->getQueue();
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
        if (!is_object($entity)) {
            $entity = unserialize($entity);
        }
        if (!is_null($associatedEntity) && !is_object($associatedEntity)) {
            $associatedEntity = unserialize($associatedEntity);
        }
        $activity->setCreationTime($ts);
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
            $associatedEntityClassSegments = explode('\\', get_class($associatedEntity));
            $associatedEntityClass = array_pop($associatedEntityClassSegments);
            $activity->setAssociatedEntityType(constant('Application\Entity\Activity::ENTITY_TYPE_' . strtoupper($associatedEntityClass)));
        }
        $activity->setData(json_encode($data));
        $server = new \Zend\Http\PhpEnvironment\RemoteAddress;
        $activity->setSourceAddress($server->getIpAddress());
        $this->em->persist($activity);
        $this->em->flush();
    }
}