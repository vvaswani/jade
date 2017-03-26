<?php
namespace Application\Entity\Permission;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Application\Entity\Permission;

/**
 * @ORM\Entity
 */
class Job extends Permission
 {
    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Job", inversedBy="permissions")
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
