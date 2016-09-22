<?php
namespace Application\Factory;

use Interop\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\Factory\FactoryInterface;

class ActivityManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $em = $container->get(EntityManager::class);
        return new $requestedName($em);
    }
}