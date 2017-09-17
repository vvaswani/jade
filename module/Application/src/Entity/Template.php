<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="template")
 * @Annotation\Name("template")
 */
 class Template
 {

    const UPLOAD_PATH = 'data/upload/templates';

    const PERMISSION_MANAGE = 'TEMPLATE.MANAGE';

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
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"template.name"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"template.description"})
     */
    protected $description;

    /**
     * @Annotation\Validator({"name":"FileExtension", "options":{"extension":"pdf,jpeg,jpg,png,doc,docx,xls,xlsx,ppt,pptx,ods,odt,odp"} })
     * @Annotation\Type("Zend\Form\Element\File")
     * @Annotation\Name("file")
     * @Annotation\Options({"label":"template.filename"})
     */
    protected $file;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Exclude()
     */
    protected $filename;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Annotation\Exclude()
     */
    protected $filenameHash;

    /**
     * @ORM\Column(type="datetime")
     * @Annotation\Exclude()
     */
    protected $creationTime;

    /**
     * @ORM\OneToMany(targetEntity="\Application\Entity\Template\Permission", mappedBy="entity", cascade={"remove", "persist"})
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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilenameHash()
    {
        return $this->filenameHash;
    }

    public function setFilenameHash($filenameHash)
    {
        $this->filenameHash = $filenameHash;
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
            $permission = new Template\Permission;
            $permission->setUser($user);
            $permission->setName(Template::PERMISSION_MANAGE);
            $permission->setTemplate($this);
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