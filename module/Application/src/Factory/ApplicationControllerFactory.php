<?php
namespace Application\Factory;

use Interop\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\Factory\FactoryInterface;
<<<<<<< HEAD
use Application\Service\ActivityStreamLogger;
=======
use Application\Service\ActivityRecorder;
>>>>>>> Added activity recording service and activity stream in case view

class ApplicationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $em = $container->get(EntityManager::class);
<<<<<<< HEAD
        $ar = $container->get(ActivityStreamLogger::class);
=======
        $ar = $container->get(ActivityRecorder::class);
>>>>>>> Added activity recording service and activity stream in case view
        return new $requestedName($em, $ar);
    }
}