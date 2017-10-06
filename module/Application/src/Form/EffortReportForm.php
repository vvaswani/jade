<?php
namespace Application\Form;

use Zend\Form\Annotation;

/**
 * @Annotation\Name("effort-search")
 */
class EffortReportForm
{

    /**
     * @Annotation\Type("DoctrineModule\Form\Element\ObjectSelect")
     * @Annotation\Required(false)
     */
    protected $entity;

    /**
     * @Annotation\Required(false)
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Type("Zend\Form\Element\Date")
     * @Annotation\Options({"label":"common.from"})
     */
    protected $from;

    /**
     * @Annotation\Required(false)
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Type("Zend\Form\Element\Date")
     * @Annotation\Options({"label":"common.to"})
     */
    protected $to;

    /**
     * @Annotation\Required(false)
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $format;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"common.confirm"})
     */
    public $submit;
}
