<?php
namespace Application\Factory;

use Interop\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\Factory\FactoryInterface;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> Added activity recording service and activity stream in case view
=======
>>>>>>> Updated service names
use Application\Service\ActivityStreamLogger;
=======
use Application\Service\ActivityRecorder;
>>>>>>> Added activity recording service and activity stream in case view
<<<<<<< HEAD
<<<<<<< HEAD
=======
use Application\Service\ActivityStreamLogger;
>>>>>>> Updated service names
=======
>>>>>>> Added activity recording service and activity stream in case view
=======
=======
use Application\Service\ActivityStreamLogger;
>>>>>>> Updated service names
>>>>>>> Updated service names
=======
use Application\Service\ActivityStreamLogger;
>>>>>>> Fixed merge conflicts

class ApplicationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $em = $container->get(EntityManager::class);
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> Updated service names
        $ar = $container->get(ActivityStreamLogger::class);
=======
        $ar = $container->get(ActivityRecorder::class);
>>>>>>> Added activity recording service and activity stream in case view
=======
        $ar = $container->get(ActivityStreamLogger::class);
>>>>>>> Updated service names
<<<<<<< HEAD
=======
        $ar = $container->get(ActivityStreamLogger::class);
=======
        $ar = $container->get(ActivityRecorder::class);
>>>>>>> Added activity recording service and activity stream in case view
>>>>>>> Added activity recording service and activity stream in case view
=======
>>>>>>> Updated service names
        return new $requestedName($em, $ar);
=======
        $asl = $container->get(ActivityStreamLogger::class);
        return new $requestedName($em, $asl);
>>>>>>> Fixed merge conflicts
    }
}