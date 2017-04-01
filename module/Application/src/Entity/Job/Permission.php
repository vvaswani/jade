<?php
namespace Application\Entity\Job;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Application\Entity\Permission as BasePermission;

/**
 * @ORM\Entity
 */
class Permission extends BasePermission
 {
    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Job", inversedBy="permissions")
     * @Annotation\Exclude()
     */
    protected $entity;

    // convenience method
    public function getJob()
    {
        return $this->getEntity();
    }

    // convenience method
    public function setJob(\Application\Entity\Job $job)
    {
        $this->setEntity($job);
    }  

 }
