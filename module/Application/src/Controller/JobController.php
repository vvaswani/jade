<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Job;

class JobController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }
}
