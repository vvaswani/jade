<?php
namespace Application\Factory\View\Helper;

use Interop\Container\ContainerInterface;
use Application\Service\AuthorizationService;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthorizationViewHelperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $acs = $container->get(AuthorizationService::class);
        return new $requestedName($acs);
    }
}