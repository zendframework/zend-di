<?php
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
