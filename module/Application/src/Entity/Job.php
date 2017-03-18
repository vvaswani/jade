<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="job")
 * @Annotation\Name("job")
 */
class Job
{

    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 0;

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
     * @Annotation\Validator({"name":"StringLength", "options":{"min":1, "max":255}})
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"job.title"})     
     */
    protected $title;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Annotation\Required(false)
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})     
     * @Annotation\Validator({"name":"StringLength", "options":{"min":1}})
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Options({"label":"job.description"})     
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Annotation\Required(false)
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})     
     * @Annotation\Validator({"name":"StringLength", "options":{"min":1}})
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Options({"label":"job.comments"})     
     */
    protected $comments;

    /**
     * @ORM\Column(type="datetime")
     * @Annotation\Exclude()
     */
    protected $created;

    /**
     * ORM\ManyToOne(targetEntity="User")
     * ORM\JoinColumn(name="created_by")
     * Annotation\Exclude()
     protected $owner;
     */

    /**
     * @ORM\Column(type="integer")
     * @Annotation\Exclude()
     */
    public $status;

    /**
     * @ORM\ManyToMany(targetEntity="Label")
     * @ORM\JoinTable(name="job_label")
     * @Annotation\Required(false)
     * @Annotation\Type("DoctrineModule\Form\Element\ObjectSelect")
     * @Annotation\Attributes({"multiple":"multiple"})
     * @Annotation\Options({"label":"job.labels", "use_hidden_element":"true"})   
     * @see https://github.com/zendframework/zendframework/issues/7298       
     */
    public $labels;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="job", cascade={"remove"})
     * @ORM\OrderBy({"created" = "DESC"})
     * @Annotation\Required(false)
     * @see http://future500.nl/articles/2013/09/more-on-one-to-manymany-to-one-associations-in-doctrine-2/
     */
    public $files;

    /**
     * @ORM\OneToMany(targetEntity="Privilege", mappedBy="job", cascade={"remove", "persist"})
     * @Annotation\Required(false)
     */
    public $privileges;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"common.save"})
     */
    public $submit;

    public function __construct() {
        $this->labels = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->privileges = new ArrayCollection();
    }    
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
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

    public function getLabels()
    {
        return $this->labels;
    }

    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    public function addLabels(ArrayCollection $labels)
    {
        foreach ($labels as $label) {
            $this->labels->add($label);
        }
    }

    public function removeLabels(ArrayCollection $labels)
    {
        foreach ($labels as $label) {
            $this->labels->removeElement($label);
        }
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function addFile(File $file)
    {
        $this->files->add($file);
    }

    public function removeFile(File $file)
    {
        $this->files->removeElement($file);
    } 

    public function getPrivileges()
    {
        return $this->privileges;
    }

    public function getUserPrivilege(User $user)
    {
        $defaultPrivilege = new Privilege();
        $defaultPrivilege->setName(Privilege::NAME_RED);
        $defaultPrivilege->setJob($this);
        $defaultPrivilege->setUser($user);
        foreach ($this->privileges as $privilege)
        {
            if ($privilege->getUser()->getId() == $user->getId())
            {
                return $privilege;
            }
        }
        return $defaultPrivilege;
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