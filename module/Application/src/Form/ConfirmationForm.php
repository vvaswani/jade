<?php
namespace Application\Form;

use Zend\Form\Annotation;

/**
 * @Annotation\Name("confirm")
 */
class ConfirmationForm
{

    /**
     * @Annotation\Type("Zend\Form\Element\Hidden")
     * @Annotation\Filter({"name":"Int"})
     * @Annotation\Attributes({"value":"1"})
     */
    protected $confirm;    

    /**
     * @Annotation\Type("Zend\Form\Element\Hidden")
     * @Annotation\Required(false)
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})     
     */
    protected $cancelTo;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"common.confirm"})
     */
    public $submit;
}
