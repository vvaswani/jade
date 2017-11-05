<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Doctrine\ORM\EntityManager;
use Application\Service\ActivityService;
use Zend\Authentication\AuthenticationService;

class LocaleController extends AbstractActionController
{

    private $as;

    private $em;

    public function __construct(EntityManager $em, ActivityService $ams, AuthenticationService $as)
    {
        $this->em = $em;
        $this->as = $as;
    }

    public function saveAction()
    {
        $locale = $this->params()->fromRoute('locale');
        $container = new Container('default');
        $container->locale = $locale;
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        $this->redirect()->toUrl($url);
    }
}
