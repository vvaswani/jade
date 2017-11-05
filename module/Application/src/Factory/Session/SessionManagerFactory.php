<?php
namespace Application\Factory\Session;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SessionManager;
use Zend\Session\Container;

class SessionManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (!isset($config['session'])) {
            $sm = new SessionManager();
            Container::setDefaultManager($sm);
            return $sm;
        }

        $session = $config['session'];

        $sessionConfig = null;
        if (isset($session['config'])) {
            $class = isset($session['config']['class'])
                ?  $session['config']['class']
                : SessionConfig::class;

            $options = isset($session['config']['options'])
                ?  $session['config']['options']
                : [];

            $sessionConfig = new $class();
            $sessionConfig->setOptions($options);
        }

        $sessionStorage = null;
        if (isset($session['storage'])) {
            $class = $session['storage'];
            $sessionStorage = new $class();
        }

        $sessionSaveHandler = null;
        if (isset($session['save_handler'])) {
            // class should be fetched from service manager
            // since it will require constructor arguments
            $sessionSaveHandler = $container->get($session['save_handler']);
        }

        $sm = new SessionManager(
            $sessionConfig,
            $sessionStorage,
            $sessionSaveHandler
        );

        Container::setDefaultManager($sm);
        return $sm;
    }
}