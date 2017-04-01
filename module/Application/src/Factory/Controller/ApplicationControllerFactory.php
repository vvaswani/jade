<?php
namespace Application\Factory\Controller;

use Interop\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Application\Service\ActivityService;
use Application\Service\AuthorizationService;
use Doctrine\ORM\Events;
use Zend\ServiceManager\Factory\FactoryInterface;

class ApplicationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $em = $container->get(EntityManager::class);
        $ams = $container->get(ActivityService::class);
        $as = $container->get('doctrine.authenticationservice.orm_default');
        $em->getEventManager()->addEventListener(
            array(Events::onFlush), $ams);
        return new $requestedName($em, $ams, $as);
    }
}