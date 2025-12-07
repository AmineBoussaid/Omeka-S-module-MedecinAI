<?php
return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'medecin-ai' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/medecin-ai',
                            'defaults' => [
                                '__NAMESPACE__' => 'MedecinAI\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'MedecinAI\Controller\Index' => 'MedecinAI\Controller\IndexController',
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Médecin AI',
                'route' => 'admin/medecin-ai',
                'resource' => 'MedecinAI\Controller\Index',
                'privilege' => 'index',
                'class' => 'medecin-ai-nav',
            ],
        ],
    ],
];
