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
     * @ORM\Column(type="datetime")
     * @Annotation\Exclude()
     */
    protected $created;

    /**
     * @ORM\Column(type="integer")
     * @Annotation\Exclude()
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="user", cascade={"remove"})
     * @ORM\OrderBy({"created" = "DESC"})
     * @Annotation\Exclude()
     */
    protected $activities;

    /**
     * @ORM\OneToMany(targetEntity="Privilege", mappedBy="user", cascade={"remove"})
     * @Annotation\Exclude()
     */
    protected $privileges;

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

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created)
    {
        $this->created = $created;
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

    public function getPrivileges()
    {
        return $this->privileges;
    }

    public function setPrivileges($privileges)
    {
        $this->privileges = $privileges;
    }

    public function addPrivilege(Privilege $privilege)
    {
        $this->privileges->add($privilege);
    }

    public function removePrivilege(Privilege $privilege)
    {
        $this->privileges->removeElement($privilege);
    }    
}