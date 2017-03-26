<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout()->setVariable('home', 'true');
        return new ViewModel();
    }

    public function dashboardAction()
    {
        return new ViewModel();
    }    
}
