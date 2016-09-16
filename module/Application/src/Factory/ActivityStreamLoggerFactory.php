<?php
namespace Application\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Doctrine\ORM\EntityManager;
use Application\Entity\Activity;
use Application\Service\ActivityStreamLogger;

class ActivityStreamLoggerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ActivityStreamLogger(
            $container->get(EntityManager::class)
        );    
    }
}