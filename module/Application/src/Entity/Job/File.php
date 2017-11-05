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

    // in other cases, the form property and ORM property are separate
    // because the form is bound with the entity and this is the only
    // option to access uploaded file data after validation
    // in this case, the form is not bound with the entity
    // and uploaded file data can be accessed directly after validation
    // therefore the form and ORM properties need not be separate
    /**
     * @ORM\Column(type="string")
     * @Annotation\Validator({"name":"FileExtension", "options":{"extension":"pdf,jpeg,jpg,png,doc,docx,xls,xlsx,ppt,pptx,ods,odt,odp"} })
     * @Annotation\Type("Zend\Form\Element\File")
     * @Annotation\Name("file")
     * @Annotation\Options({"label":"file.name"})
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
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Job", inversedBy="files")
     * @see http://future500.nl/articles/2013/09/more-on-one-to-manymany-to-one-associations-in-doctrine-2/
     */
     protected $job;

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

    public function getJob()
    {
        return $this->job;
    }

    public function setJob(\Application\Entity\Job $job)
    {
        $this->job = $job;
    }
}