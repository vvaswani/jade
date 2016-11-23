<?php
namespace Application\Factory\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AccessControlServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $as = $container->get('doctrine.authenticationservice.orm_default');
        return new $requestedName($as);
    }
}