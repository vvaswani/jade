<?php
namespace Application\Factory\Service;

use Interop\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class ActivityServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $em = $container->get(EntityManager::class);
        $as = $container->get('doctrine.authenticationservice.orm_default');
        return new $requestedName($em, $as);
    }
}