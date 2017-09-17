<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    const ROLE_ADMINISTRATOR = 'ADMINISTRATOR';
    const ROLE_EMPLOYEE = 'EMPLOYEE';
    const ROLE_CUSTOMER = 'CUSTOMER';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Annotation\Validator({"name":"EmailAddress"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"user.username"})
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"user.password"})
     */
    protected $password;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"user.name"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"user.role"})
     * @Annotation\Required(false)
     */
    protected $role;

    /**
     * @ORM\Column(type="datetime")
     * @Annotation\Exclude()
     */
    protected $creationTime;

    /**
     * @ORM\Column(type="integer")
     * @Annotation\Exclude()
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="user", cascade={"remove"})
     * @ORM\OrderBy({"creationTime" = "DESC"})
     * @Annotation\Exclude()
     */
    protected $activities;

    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Permission", mappedBy="user", cascade={"remove", "persist"})
     * @Annotation\Exclude()
     */
    protected $permissions;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getActivities()
    {
        return $this->activities;
    }

    public function setActivities($activities)
    {
        $this->activities = $activities;
    }

    public function addActivity(Activity $activity)
    {
        $this->activities->add($activity);
    }

    public function removeActivity(Activity $activity)
    {
        $this->activities->removeElement($activity);
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

}