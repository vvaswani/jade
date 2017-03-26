<?php
namespace Application\Entity\Permission;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Application\Entity\Permission;

/**
 * @ORM\Entity
 */
class User extends Permission
 {
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\User", inversedBy="permissions")
     */
    protected $entity;

    // convenience method
    public function getUser()
    {
        return $this->getEntity();
    }

    // convenience method
    public function setUser(\Application\Entity\User $user)
    {
        $this->setEntity($user);
    }  
}
