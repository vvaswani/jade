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

        $alertPlugin = $controller->plugin('alertPlugin');

        if (is_object($entity)) {
            $entityClassSegments = explode('\\', get_class($entity));     
            $entityName = array_pop($entityClassSegments);            
        } else {
            $entityName = $entity;
        }

        $controllerClassSegments = explode('\\', get_class($controller));     
        $controllerClass = array_pop($controllerClassSegments);
        $controllerName = substr($controllerClass, 0, -10);

        switch (strtoupper($entityName)) {
        	case 'JOB':
                $acl = $this->acs->getAcl();
                $job = $entity;
		        $privilege = $job->getUserPrivilege($this->as->getIdentity());
		        if (!$acl->isAllowed($privilege->getName(), 'job', strtolower($controllerName) . '.' . $actionName)) {
		            return false; 
		        }
                break;	
            case 'SYSTEM':
                $acl = $this->acs->getSystemAcl();
                $user = $this->as->getIdentity();
                if (!$acl->isAllowed($user->getRole(), 'system', strtolower($controllerName) . '.' . $actionName)) {
                    return false; 
                }
                break;  
        }
    }
}
