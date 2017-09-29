<?php
namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Application\Factory\Controller\ApplicationControllerFactory;
use Application\Factory\Controller\Plugin\AuthorizationControllerPluginFactory;
use Application\Factory\View\Helper\AuthorizationViewHelperFactory;
use Application\Entity\Job;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'dashboard' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/dashboard',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'dashboard',
                    ],
                ],
            ],
            'jobs' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/jobs[/:action][/:id][/:status]',
                    'defaults' => [
                        'controller' => Controller\JobController::class,
                        'action'     => 'index',
                        'status'     => Job::STATUS_OPEN,
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                        'status' => '[0-1]',
                    ],
                ],
            ],
            'labels' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/labels[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\LabelController::class,
                        'action'     => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                    ],
                ],
            ],
            'jobs.files' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/jobs/view/:jid/files/:action[/:id]',
                    'defaults' => [
                        'controller' => Controller\Job\FileController::class,
                        'action'     => 'save',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                        'jid'    => '[0-9]*',
                    ],
                ],
            ],
            'jobs.permissions' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/jobs/view/:jid/permissions/:action[/:id]',
                    'defaults' => [
                        'controller' => Controller\Job\PermissionController::class,
                        'action'     => 'grant',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                        'jid'    => '[0-9]*',
                    ],
                ],
            ],
            'jobs.logs' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/jobs/view/:jid/logs/:action[/:id]',
                    'defaults' => [
                        'controller' => Controller\Job\LogController::class,
                        'action'     => 'save',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                        'jid'    => '[0-9]*',
                    ],
                ],
            ],
            'users' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/users[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                    ],
                ],
            ],
            'config' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/settings',
                    'defaults' => [
                        'controller' => Controller\ConfigController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'login' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'templates' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/templates[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\TemplateController::class,
                        'action'     => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
            'Application\Service\AuthorizationService' => 'Application\Factory\Service\AuthorizationServiceFactory',
            'Application\Service\ActivityService' => 'Application\Factory\Service\ActivityServiceFactory',
        ],
        'invokables' => [
            'Doctrine\ORM\Mapping\UnderscoreNamingStrategy' => 'Doctrine\ORM\Mapping\UnderscoreNamingStrategy',
        ],
    ],
    'translator' => [
        'locale' => 'en_GB',
        'translation_file_patterns' =>  [
            [
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => ApplicationControllerFactory::class,
            Controller\JobController::class  => ApplicationControllerFactory::class,
            Controller\LabelController::class => ApplicationControllerFactory::class,
            Controller\Job\FileController::class => ApplicationControllerFactory::class,
            Controller\Job\LogController::class => ApplicationControllerFactory::class,
            Controller\Job\PermissionController::class => ApplicationControllerFactory::class,
            Controller\UserController::class => ApplicationControllerFactory::class,
            Controller\ConfigController::class => ApplicationControllerFactory::class,
            Controller\TemplateController::class => ApplicationControllerFactory::class,
        ]
    ],
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\AlertPlugin::class => InvokableFactory::class,
            Controller\Plugin\AuthorizationPlugin::class => AuthorizationControllerPluginFactory::class,
            Controller\Plugin\ConfirmationPlugin::class => InvokableFactory::class,
        ],
        'aliases' => [
            'alertPlugin' => Controller\Plugin\AlertPlugin::class,
            'authorizationPlugin' => Controller\Plugin\AuthorizationPlugin::class,
            'confirmationPlugin' => Controller\Plugin\ConfirmationPlugin::class,
        ]
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\Authorize::class => AuthorizationViewHelperFactory::class,
        ],
       'aliases' => [
            'authorize' => View\Helper\Authorize::class
       ]
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'naming_strategy' => 'Doctrine\ORM\Mapping\UnderscoreNamingStrategy'
            ],
        ],
        'fixture' => [
            'Application' => __DIR__ . '/../src/Fixture',
        ]
    ],
];
