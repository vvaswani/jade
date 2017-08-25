<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="activity")
 */
class Activity
{

    const OPERATION_CREATE = 'CREATE';
    const OPERATION_UPDATE = 'UPDATE';
    const OPERATION_DELETE = 'DELETE';
    const OPERATION_ASSOCIATE = 'ASSOCIATE';
    const OPERATION_DISSOCIATE = 'DISSOCIATE';
    const OPERATION_REQUEST = 'REQUEST';
    const OPERATION_LOGIN = 'LOGIN';
    const OPERATION_LOGOUT = 'LOGOUT';
    const OPERATION_GRANT = 'GRANT';
    const OPERATION_REVOKE = 'REVOKE';

    const ENTITY_TYPE_JOB = 'JOB';
    const ENTITY_TYPE_LABEL = 'LABEL';
    const ENTITY_TYPE_FILE = 'FILE';
    const ENTITY_TYPE_USER = 'USER';
    const ENTITY_TYPE_TEMPLATE = 'TEMPLATE';
    
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationTime;
        
    /**
     * @ORM\Column(type="string")
     */
    protected $operation;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $entityType;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $entityId;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $associatedEntityType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $associatedEntityId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="activities")
     */
    protected $user;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $data;

    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }    

    public function getOperation()
    {
        return $this->operation;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;
    }
    
    public function getEntityType()
    {
        return $this->entityType;
    }

    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;
    }    

    public function getEntityId()
    {
        return $this->entityId;
    }

    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }
    
    public function getAssociatedEntityId()
    {
        return $this->associatedEntityId;
    }

    public function setAssociatedEntityId($associatedEntityId)
    {
        $this->associatedEntityId = $associatedEntityId;
    }

    public function getAssociatedEntityType()
    {
        return $this->associatedEntityType;
    }

    public function setAssociatedEntityType($associatedEntityType)
    {
        $this->associatedEntityType = $associatedEntityType;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

}