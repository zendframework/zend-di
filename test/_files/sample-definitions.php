<?php
return [
    'section-a' => [
        'di' => [
            'instance' => [
                'alias' => [
                    'my-repository' => 'My\RepositoryA',
                    'my-mapper' => 'My\Mapper',
                    'my-dbAdapter' => 'My\DbAdapter',
                ],
                'preferences' => [
                    'my-repository' => [ 'my-mapper' ],
                    'my-mapper' => [ 'my-dbAdapter' ],
                ],
                'My\DbAdapter' => [
                    'parameters' => [
                        'username' => 'readonly',
                        'password' => 'mypassword',
                    ],
                ],
                'my-dbAdapter' => [
                    'parameters' => [
                        'username' => 'readwrite',
                    ],
                ],
            ],
        ],
    ],
    'section-b' => [
        'di' => [
            'definitions' => [
                1 => [
                    'class' => Zend\Di\Definition\BuilderDefinition::class,
                    'My\DbAdapter' => [
                        'methods' => [
                            '__construct' => [
                                'username' => null,
                                'password' => null,
                            ],
                        ],
                    ],
                    'My\Mapper' => [
                        'methods' => [
                            '__construct' => [
                                'dbAdapter' => 'My\DbAdapter',
                            ],
                        ],
                    ],
                    'My\Repository' => [
                        'methods' => [
                            '__construct' => [
                                'mapper' => 'My\Mapper',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'section-c' => [
        'di' => [
            'definition' => [
                'runtime' => [
                    'xxx' => 'zzz',
                ],
            ],
        ],
    ],
    'section-d' => [
        'di' => [
            'definition' => [
                'runtime' => [
                    'enabled' => false,
                ],
            ],
        ],
    ],
    'section-e' => [
        'di' => [
            'definition' => [
                'runtime' => [
                    'use_annotations' => true,
                ],
            ],
        ],
    ],
];
