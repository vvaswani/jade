<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="label")
 * @Annotation\Name("label")
 */
 class Label
 {

    const PERMISSION_MANAGE = 'LABEL.MANAGE';

    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Annotation\Exclude()
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})     
     * @Annotation\Validator({"name":"StringLength", "options":{"min":1, "max":50}})
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"label.name"})     
     */
    protected $name;     
    
    
    /**
     * @ORM\Column(type="string")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^#[0-9a-f]{3}([0-9a-f]{3})?$/"}})
     * @Annotation\Type("Zend\Form\Element\Color")
     * @Annotation\Options({"label":"label.colour"})     
     */
    protected $colour;
    
    /**
     * @ORM\Column(type="datetime")
     * @Annotation\Exclude()
     */
    protected $creationTime;

    /**
     * @ORM\OneToMany(targetEntity="\Application\Entity\Permission\Label", mappedBy="entity", cascade={"remove", "persist"})
     * @Annotation\Exclude()
     */
    protected $permissions;
    
    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"common.save"})
     */
    public $submit;    

    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getColour()
    {
        return $this->colour;
    }

    public function setColour($colour)
    {
        $this->colour = $colour;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    public function addPermission(Permission $permission)
    {
        $this->permissions->add($permission);
    }

    public function removePermission(Permission $permission)
    {
        $this->permissions->removeElement($permission);
    } 

    public function getUserPermissions(User $user)
    {
        $permissions = array();
        if ($user->getRole() == User::ROLE_ADMINISTRATOR) {
            $permission = new Permission\Label;
            $permission->setUser($user);
            $permission->setName(Label::PERMISSION_MANAGE);
            $permission->setLabel($this);
            $permissions[] = $permission;
        } 
        foreach ($this->permissions as $permission) {
            if ($permission->getUser()->getId() == $user->getId()) {
                $permissions[] = $permission;
            }
        }        
        return $permissions;
    }    
}

