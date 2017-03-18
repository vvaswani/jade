<?php
namespace Application\Factory\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Application\Service\AuthorizationService;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthorizationControllerPluginFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $acs = $container->get(AuthorizationService::class);
        $as = $container->get('doctrine.authenticationservice.orm_default');
        return new $requestedName($as, $acs);
    }
}