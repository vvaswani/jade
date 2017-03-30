<?php
namespace Application\Entity\Label;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Application\Entity\Permission as BasePermission;

/**
 * @ORM\Entity
 */
class Permission extends BasePermission
{

    public function __construct()
    {
        $this->setEntityType(Permission::ENTITY_TYPE_LABEL);
    }

    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Label", inversedBy="permissions")
     * @Annotation\Exclude()
     */
    protected $entity;

    // convenience method
    public function getLabel()
    {
        return $this->getEntity();
    }

    // convenience method
    public function setLabel(\Application\Entity\Label $label)
    {
        $this->setEntity($label);
    }
}
