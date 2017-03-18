<?php
namespace Application\Factory\Controller;

use Interop\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Application\Service\ActivityManagerService;
use Application\Service\AuthorizationService;
use Application\Listener\ActivityListener;
use Zend\ServiceManager\Factory\FactoryInterface;

class ApplicationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $em = $container->get(EntityManager::class);
        $ams = $container->get(ActivityManagerService::class);
        $al = $container->get(ActivityListener::class);
        $as = $container->get('doctrine.authenticationservice.orm_default');
        $acs = $container->get(AuthorizationService::class);
        return new $requestedName($em, $ams, $al, $as, $acs);
    }
}