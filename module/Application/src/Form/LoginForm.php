<?php
namespace Application\Form;

use Zend\Form\Annotation;

/**
 * @Annotation\Name("login")
 */
class LoginForm
{

    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Validator({"name":"EmailAddress"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"user.username"})     
     */
    protected $username;    

    /**
     * @Annotation\Type("Zend\Form\Element\Password")
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})     
     * @Annotation\Options({"label":"user.password"})     
     */
    protected $password;

    /**
     * @Annotation\Type("Zend\Form\Element\Hidden")
     * @Annotation\Required(false)
     * @Annotation\Filter({"name":"UriNormalize"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     */
    protected $url;    

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"common.confirm"})
     */
    public $submit;
}
