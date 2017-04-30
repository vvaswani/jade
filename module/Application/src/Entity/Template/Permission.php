<?php
namespace Application\Entity\Template;

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
        $this->setEntityType(Permission::ENTITY_TYPE_TEMPLATE);
    }

    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Template", inversedBy="permissions")
     * @Annotation\Exclude()
     */
    protected $entity;

    // convenience method
    public function getTemplate()
    {
        return $this->getEntity();
    }

    // convenience method
    public function setTemplate(\Application\Entity\Template $template)
    {
        $this->setEntity($template);
    }
}
