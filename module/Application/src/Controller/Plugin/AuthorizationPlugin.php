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

    public function isAuthorized($user = null, $controllerName = null, $actionName = null, $entity = null)
    {
        $controller = $this->getController();

        if (!$controller || !method_exists($controller, 'plugin')) {
            throw new Exception\DomainException('Authorization plugin requires a controller that defines the plugin() method');
        }

        $event = $controller->getEvent();

        if (is_null($controllerName)) {        
            $controllerClassSegments = explode('\\', get_class($controller));     
            $controllerClass = array_pop($controllerClassSegments);
            $controllerName = substr($controllerClass, 0, -10);
        }

        if (is_null($actionName)) {        
            $actionName = $event->getRouteMatch()->getParam('action', null);
        }

        if (is_null($user)) {
            $user = $this->as->getIdentity();
        }

        return $this->acs->isAuthorized($user, $controllerName, $actionName, $entity);
    }
}
