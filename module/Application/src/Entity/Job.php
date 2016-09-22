<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Application\Entity\Activity;

/**
 * @ORM\Entity
 * @ORM\Table(name="job")
 * @Annotation\Name("job")
 */
class Job
{

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
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"common.save"})
     */
    public $submit;

    public function __construct() {
        $this->labels = new ArrayCollection();
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
    
}