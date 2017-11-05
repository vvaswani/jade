<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Application\Controller\UserController;

class Module
{
    const VERSION = '3.0.2dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap($event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        $sharedEventManager->attach(AbstractActionController::class,
            $event::EVENT_DISPATCH, [$this, 'onDispatch'], 100);
        $sharedEventManager->attach(AbstractActionController::class,
            $event::EVENT_RENDER, [$this, 'registerCsvStrategy'], 100);

        $this->configureSessions($event);

        $this->configureLocales($event);

    }

    public function onDispatch($event)
    {
        $controller = $event->getTarget();
        $controllerName = $event->getRouteMatch()->getParam('controller', null);
        $actionName = $event->getRouteMatch()->getParam('action', null);

        $actionName = str_replace('-', '', lcfirst(ucwords($actionName, '-')));
        $as = $event->getApplication()->getServiceManager()
        			->get('doctrine.authenticationservice.orm_default');

        if (!(($controllerName == 'Application\Controller\UserController' && $actionName == 'login') || ($controllerName == 'Application\Controller\IndexController'  && $actionName == 'index') || ($controllerName == 'Application\Controller\LocaleController'  && $actionName == 'save') ) && !$as->hasIdentity()) {
            $uri = $event->getApplication()->getRequest()->getUri();
            $uri->setScheme(null)
                ->setHost(null)
                ->setPort(null)
                ->setUserInfo(null);
            $redirectUri = $uri->toString();
            return $controller->redirect()->toRoute('login', [],
                    ['query' => ['continue' => $redirectUri]]);
        }
    }

    public function registerCsvStrategy(MvcEvent $e)
    {
        $app          = $e->getTarget();
        $locator      = $app->getServiceManager();
        $view         = $locator->get('Zend\View\View');
        $csvStrategy  = $locator->get('Application\View\Csv\Strategy');
        $view->getEventManager()->attach($csvStrategy, 100);
    }

    private function configureLocales(MvcEvent $e)
    {
        // add list of available locale files
        // as view model variable
        $viewModel = $e->getViewModel();
        $availableLocales = [];
        foreach (glob(__DIR__ . "/../language/*.php") as $filename) {
            $strings = include $filename;
            $language = $strings['language.name'];
            unset($strings);
            $availableLocale = basename($filename, '.php');
            $availableLocales[$availableLocale] = $language;
        }
        $viewModel->availableLocales = $availableLocales;

        // get the default locale specified in the application configuration
        $translator = $e->getApplication()->getServiceManager()->get('MvcTranslator');
        $configuredLocale = $translator->getLocale();

        // check that the locale file exists
        // throw an exception if not
        if (!in_array($configuredLocale, array_keys($availableLocales))) {
            throw new \Exception('Configured locale [' . $configuredLocale . '] is not available');
        }

        // set the runtime locale to the default locale
        \Locale::setDefault($configuredLocale);

        // check if a user session exists with a user-specified locale
        // if it does, check that the locale file exists
        // if it does, reset the runtime locale to the user-specified locale
        $container = new Container('default');
        if (isset($container->locale) && in_array($container->locale, array_keys($availableLocales))) {
            $translator->setLocale($container->locale);
            \Locale::setDefault($container->locale);
        }
    }

    private function configureSessions(MvcEvent $e)
    {
        $session = $e->getApplication()->getServiceManager()->get('SessionManager');
        $session->start();

        $container = new Container('default');

        if (isset($container->init)) {
            return;
        }

        $serviceManager = $e->getApplication()->getServiceManager();
        $request        = $e->getRequest();

        $session->regenerateId(true);
        $container->init          = 1;
        $container->remoteAddr    = $request->getServer()->get('REMOTE_ADDR');
        $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

        $config = $serviceManager->get('config');
        if (!isset($config['session'])) {
            return;
        }

        $sessionConfig = $config['session'];

        if (!isset($sessionConfig['validators'])) {
            return;
        }

        $chain = $session->getValidatorChain();

        foreach ($sessionConfig['validators'] as $validator) {
            switch ($validator) {
                case Validator\HttpUserAgent::class:
                    $validator = new $validator($container->httpUserAgent);
                    break;
                case Validator\RemoteAddr::class:
                    $validator  = new $validator($container->remoteAddr);
                    break;
                default:
                    $validator = new $validator();
            }

            $chain->attach('session.validate', array($validator, 'isValid'));
        }
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'Zend\Authentication\AuthenticationService' => function ($serviceManager) {
                    return $serviceManager->get('doctrine.authenticationservice.orm_default');
                },
            ],
        ];
    }

}
