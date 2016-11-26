<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Controller\UserController;

class Module
{
    const VERSION = '3.0.2dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }  

    public function onBootstrap($event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        $sharedEventManager->attach(AbstractActionController::class, 
            $event::EVENT_DISPATCH, [$this, 'onDispatch'], 100);
    }
    
    public function onDispatch($event)
    {
        $controller = $event->getTarget();
        $controllerName = $event->getRouteMatch()->getParam('controller', null);
        $actionName = $event->getRouteMatch()->getParam('action', null);
        
        $actionName = str_replace('-', '', lcfirst(ucwords($actionName, '-')));
        $as = $event->getApplication()->getServiceManager()
        			->get('doctrine.authenticationservice.orm_default');

        if (!(($controllerName == 'Application\Controller\UserController' && $actionName == 'login') || ($controllerName == 'Application\Controller\IndexController')) && !$as->hasIdentity()) {
            $uri = $event->getApplication()->getRequest()->getUri();
            $uri->setScheme(null)
                ->setHost(null)
                ->setPort(null)
                ->setUserInfo(null);
            $redirectUri = $uri->toString();
            return $controller->redirect()->toRoute('login', [], 
                    ['query' => ['url' => $redirectUri]]);
        }
    }  

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'Zend\Authentication\AuthenticationService' => function ($serviceManager) {
                    return $serviceManager->get('doctrine.authenticationservice.orm_default');
                },
                /*
                'DoctrineModule\Validator\UniqueObject' => function ($serviceManager) {
                    $uniqueObject = new DoctrineModule\Validator\UniqueObject(array(
                        'fields' => 'username',
                        'object_repository' => $serviceManager->get('Doctrine\ORM\EntityManager')->getRepository('Application\Entity\User'),
                        'object_manager' => $serviceManager->get('Doctrine\ORM\EntityManager'),
                    ));
                    return $uniqueObject;
                },
                */          
            ],
        ];
    }      
}
