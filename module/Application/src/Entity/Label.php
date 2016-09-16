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
     * @Annotation\Attributes({"type":"Zend\Form\Element\Text"})
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
     * @Annotation\Options({"label":"common.name"})     
=======
     * @Annotation\Options({"label":"application.label.name"})     
>>>>>>> Added activity recording service and activity stream in case view
=======
=======
>>>>>>> Added activity recording service and activity stream in case view
=======
>>>>>>> Fixed merge conflicts
     * @Annotation\Options({"label":"application.label.name"})     
=======
     * @Annotation\Options({"label":"common.name"})     
>>>>>>> Removed prefix from translation keys
<<<<<<< HEAD
>>>>>>> Removed prefix from translation keys
=======
=======
     * @Annotation\Options({"label":"application.label.name"})     
>>>>>>> Added activity recording service and activity stream in case view
<<<<<<< HEAD
>>>>>>> Added activity recording service and activity stream in case view
=======
=======
     * @Annotation\Options({"label":"common.name"})     
>>>>>>> Fixed merge conflicts
>>>>>>> Fixed merge conflicts
     */
    protected $name;     
    
    
    /**
     * @ORM\Column(type="string")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Validator({"name":"Regex", "options":{"pattern":"/^#[0-9a-f]{3}([0-9a-f]{3})?$/"}})
     * @Annotation\Attributes({"type":"Zend\Form\Element\Color"})
     * @Annotation\Options({"label":"label.colour"})     
     */
    protected $colour;
    
    /**
     * @ORM\Column(type="datetime")
     * @Annotation\Exclude()
     */
    protected $created;
    
    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Submit"})
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

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }    
}