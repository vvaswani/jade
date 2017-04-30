<?php
namespace Application\Entity\Job;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @ORM\Table(name="job_file")
 * @Annotation\Name("file")
 */
 class File
 {

    const UPLOAD_PATH = 'data/upload/jobs';

    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Annotation\Exclude()
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @Annotation\Filter({"name":"FileRenameUpload", "options":{"use_upload_name":true, "use_upload_extension":true}})
     * @Annotation\Validator({"name":"FileExtension", "options":{"extension":"pdf,jpeg,jpg,png,doc,docx,xls,xlsx,ppt,pptx,ods,odt,odp"} })
     * @Annotation\Type("Zend\Form\Element\File")
     * @Annotation\Name("file")
     * @Annotation\Options({"label":"file.name"})     
     */
    protected $name;
    
    /**
     * @ORM\Column(type="datetime")
     * @Annotation\Exclude()
     */
    protected $creationTime;

    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Job", inversedBy="files")
     * @see http://future500.nl/articles/2013/09/more-on-one-to-manymany-to-one-associations-in-doctrine-2/
     */
     protected $job;

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
}