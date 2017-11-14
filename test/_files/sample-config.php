<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

return [
    'preferences' => [
        'A' => 'GlobalA',
        'B' => 'GlobalB'
    ],
    'types' => [
        'Foo' => [
            'preferences' => [
                'A' => 'LocalA',
             ],
            'parameters' => [
                'a' => '*'
            ]
        ],
        'Bar' => [
            'typeOf' => 'Foo',
            'preferences' => [
                'B' => 'LocalB'
            ]
        ]
    ],

    'arbitaryKey' => 'value',
    'factories' => [
        'should be' => [
            'ignored' => 'as well'
        ]
    ]
];
