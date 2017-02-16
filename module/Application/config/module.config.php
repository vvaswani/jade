<?php
namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Application\Factory\Controller\ApplicationControllerFactory;
use Application\Factory\Controller\UserControllerFactory;

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
            'jobs' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/jobs[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\JobController::class,
                        'action'     => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
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
            'files' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/jobs/:jid/files[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\FileController::class,
                        'action'     => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]*',
                        'jid'     => '[0-9]*',
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
        ],
    ],
    'service_manager' => [
        'factories' => [
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
            'Application\Service\ActivityManagerService' => 'Application\Factory\Service\ActivityManagerServiceFactory',
            'Application\Listener\ActivityListener' => 'Application\Factory\Listener\ActivityListenerFactory',
            'Application\Service\AccessControlService' => 'Application\Factory\Service\AccessControlServiceFactory',
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
            Controller\IndexController::class => InvokableFactory::class,
            Controller\JobController::class  => ApplicationControllerFactory::class,
            Controller\LabelController::class => ApplicationControllerFactory::class,
            Controller\FileController::class => ApplicationControllerFactory::class,
            Controller\UserController::class => ApplicationControllerFactory::class,
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
