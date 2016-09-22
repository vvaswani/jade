<?php
namespace Application\Listener;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManager;
use Application\Entity\Job;
use Application\Entity\Label;
use Application\Entity\Activity;
use Application\Entity\User;
use Application\Service\ActivityManager;

class ActivityListener 
{

    private $queue;

	public function __construct()
	{
        $this->queue = array();
        // TODO replace with authenticated user
        $this->user = new User();
        $this->user->setId(1);
	}

    /**
    * @see http://stackoverflow.com/questions/15311083/whats-the-proper-use-of-unitofwork-getscheduledcollectiondeletions-in-doctr
    */
    public function onFlush(OnFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Job || $entity instanceof Label) {
                    $this->queue[] = array(
                        $this->user, 
                        Activity::OPERATION_CREATE, 
                        new \DateTime("now"),
                        $entity,
                        null, 
                        null
                    ); 
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
        	if ($entity instanceof Job || $entity instanceof Label) {
	    		$diff = $uow->getEntityChangeSet($entity);
                if (!empty($diff)) {
                    $this->queue[] = array(
                        $this->user, 
    	                Activity::OPERATION_UPDATE, 
                        new \DateTime("now"),
    	                $entity,
    	                null, 
    	                $diff
    	            ); 
                }
		    }
        }


        foreach ($uow->getScheduledEntityDeletions() as $entity) {
	    	if ($entity instanceof Job || $entity instanceof Label) {
                $clone = clone $entity;
                $this->queue[] = array(
                    $this->user, 
	                Activity::OPERATION_DELETE, 
                    new \DateTime("now"),
	                $clone,
	                null, 
	                null
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
                            $this->user, 
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
                            $this->user, 
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
       
}