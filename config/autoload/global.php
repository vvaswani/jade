<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return [
    'doctrine' => [
        'driver' => [
            'annotation_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../../module/Application/src/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'Application\\Entity' => 'annotation_driver',
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'generate_proxies' => true,
                'proxy_dir' => 'data/tmp/DoctrineORMModule/Proxy',
            ],
        ],
        'authentication' => [
            'orm_default' => [
                'object_manager' => 'Doctrine\ORM\EntityManager',
                'identity_class' => 'Application\Entity\User',
                'identity_property' => 'username',
                'credential_property' => 'password',
                'credential_callable' => 'Application\Controller\UserController::verifyCredential'
            ],
        ],
    ],
    'session' => [
        'config' => [
            'class' => Zend\Session\Config\SessionConfig::class,
            'options' => [
                'cookie_lifetime' => 3600,
                'gc_maxlifetime' => 2592000,
            ]
        ],
        'storage' => Zend\Session\Storage\SessionArrayStorage::class,
        'validators' => [
            Zend\Session\Validator\RemoteAddr::class,
            Zend\Session\Validator\HttpUserAgent::class,
        ],
    ],
];
