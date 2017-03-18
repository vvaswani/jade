<?php
namespace Application\Controller\Plugin; 

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Authentication\AuthenticationService;
use Application\Service\AuthorizationService;

class AuthorizationPlugin extends AbstractPlugin 
{

    private $as;

    private $acs;

    public function __construct(AuthenticationService $as, AuthorizationService $acs)
    {
        $this->as = $as;
        $this->acs = $acs;
    }

    public function authorize($entity)
    {
        $controller = $this->getController();

        if (!$controller || !method_exists($controller, 'plugin')) {
            throw new Exception\DomainException('Authorization plugin requires a controller that defines the plugin() method');
        }

        $event = $controller->getEvent();
        $actionName = $event->getRouteMatch()->getParam('action', null);

        $acl = $this->acs->getAcl();

        $alertPlugin = $controller->plugin('alertPlugin');

        $entityClassSegments = explode('\\', get_class($entity));     
        $entityClass = array_pop($entityClassSegments);
        switch (strtoupper($entityClass)) {
        	case 'JOB':
		        $privilege = $entity->getUserPrivilege($this->as->getIdentity());
		        if (!$acl->isAllowed($privilege->getName(), 'job', $actionName)) {
		            return false; 
		        }    	
        }
    }
}
