<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Application\Service\ActivityService;
use Application\Service\AuthorizationService;
use Application\Entity\User;

class ConfigController extends AbstractActionController
{

    private $ams;

    private $as;

    public function __construct(EntityManager $em, ActivityService $ams, AuthenticationService $as, AuthorizationService $acs)
    {
        $this->ams = $ams;
        $this->as = $as;
    }

    public function indexAction()
    {
		$identity = $this->as->getIdentity();
		if ($identity->getRole() != User::ROLE_ADMINISTRATOR) {
			return $this->alertPlugin()->alert('common.alert-access-denied', array(), $this->url()->fromRoute('home'));
		}
        return new ViewModel();
    }
}
