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
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
 
    /**
     * @ORM\Column(type="string")
     */
    protected $type;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $caseId;

    /**
     * @ORM\Column(type="integer")
     */
    protected $userId;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $data;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getCaseId()
    {
        return $this->caseId;
    }

    public function setCaseId($caseId)
    {
        $this->caseId = $caseId;
    }
    
    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }
    
}