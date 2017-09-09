<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
use Application\Service\ActivityService;
use Zend\Authentication\AuthenticationService;

class IndexController extends AbstractActionController
{

    private $as;

    public function __construct(EntityManager $em, ActivityService $ams, AuthenticationService $as)
    {
        $this->as = $as;
    }

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
