<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="job_privilege")
 * @Annotation\Name("privilege")
 */
 class Privilege
 {

    // owner with full access
    const NAME_GREEN = 'GREEN';

    // collaborator with full access
    const NAME_ORANGE = 'ORANGE';

    // collaborator with read-only access
    const NAME_YELLOW = 'YELLOW';

    // no access
    const NAME_RED = 'RED';

    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Annotation\Exclude()
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Job", inversedBy="privileges", cascade={"persist"})
     */
     protected $job;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="privileges")
     */
     protected $user;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;


    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }       
}