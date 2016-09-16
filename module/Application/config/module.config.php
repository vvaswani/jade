<?php
namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Application\Factory\ApplicationControllerFactory;

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
        ],
    ],
    'service_manager' => [
        'factories' => [
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> Added activity recording service and activity stream in case view
=======
>>>>>>> Updated service names
            'Application\Service\ActivityStreamLogger' => 'Application\Factory\ActivityStreamLoggerFactory',            
=======
            'Application\Service\ActivityRecorder' => 'Application\Factory\ActivityRecorderFactory',            
>>>>>>> Added activity recording service and activity stream in case view
<<<<<<< HEAD
<<<<<<< HEAD
=======
            'Application\Service\ActivityStreamLogger' => 'Application\Factory\ActivityStreamLoggerFactory',            
>>>>>>> Updated service names
=======
>>>>>>> Added activity recording service and activity stream in case view
=======
=======
            'Application\Service\ActivityStreamLogger' => 'Application\Factory\ActivityStreamLoggerFactory',            
>>>>>>> Updated service names
>>>>>>> Updated service names
=======
            'Application\Service\ActivityStreamLogger' => 'Application\Factory\ActivityStreamLoggerFactory',
>>>>>>> Fixed merge conflicts
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
            Controller\JobController::class   => ApplicationControllerFactory::class,
            Controller\LabelController::class => ApplicationControllerFactory::class,
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
    ],    
];
