<?php
namespace Application\Entity\Job;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="job_log")
 * @Annotation\Name("log")
 */
 class Log
 {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Annotation\Exclude()
     */
    protected $id;

    /**
     * @ORM\Column(type="date")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Type("Zend\Form\Element\Date")
     * @Annotation\Options({"label":"log.date"})
     */
    protected $date;

    /**
     * @ORM\Column(type="float")
     * @Annotation\Filter({"name":"NumberParse"})
     * @Annotation\Validator({"name":"Float"})
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"log.hours"})
     */
     protected $effort;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":1}})
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"log.description"})
     */
     protected $description;

    /**
     * @ORM\Column(type="datetime")
     * @Annotation\Exclude()
     */
    protected $creationTime;

    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Job", inversedBy="logs")
     * @see http://future500.nl/articles/2013/09/more-on-one-to-manymany-to-one-associations-in-doctrine-2/
     */
     protected $job;

    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\User")
     * @Annotation\Exclude()
     */
     protected $user;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"common.save"})
     */
    public $submit;

    /**
     * @Annotation\Type("Zend\Form\Element\Csrf")
     */
    public $csrf;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getEffort()
    {
        return $this->effort;
    }

    public function setEffort($effort)
    {
        $this->effort = $effort;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }

    public function setCreationTime($creationTime)
    {
        $this->creationTime = $creationTime;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function setJob(\Application\Entity\Job $job)
    {
        $this->job = $job;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(\Application\Entity\User $user)
    {
        $this->user = $user;
    }
}