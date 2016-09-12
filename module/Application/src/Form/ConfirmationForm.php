<?php
namespace Application\Form;

use Zend\Form\Annotation;

/**
 * @Annotation\Name("confirm")
 */
class ConfirmationForm
{

    /**
     * @Annotation\Filter({"name":"Int"})
     * @Annotation\Attributes({"type":"Zend\Form\Element\Hidden"})
     */
    protected $confirmed;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"application.common.confirm"})
     */
    public $submit;  

    private $routeName;

    private $actionName;

    /*
    public function __construct($routeName, $actionName) 
    {
    	$this->setRouteName($routeName);
    	$this->setActionName($actionName);
    	return $this;
    }
    */

    public function setRouteName($routeName)
    {
    	$this->routeName = $routeName;
    }  

    public function getRouteName($routeName)
    {
    	return $this->routeName;
    }  

    public function setActionName($actionName)
    {
    	$this->actionName = $actionName;
    }  

    public function getActionName($actionName)
    {
    	return $this->actionName;
    }     

}
