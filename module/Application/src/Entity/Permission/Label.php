<?php
namespace Application\Entity\Permission;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Application\Entity\Permission;

/**
 * @ORM\Entity
 */
class Label extends Permission
{

    public function __construct()
    {
        $this->setEntityType(Permission::ENTITY_TYPE_LABEL);
    }

    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Label", inversedBy="permissions")
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
