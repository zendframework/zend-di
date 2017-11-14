<?php

namespace ZendTest\Di;

return [
    'expect' => [
        // Requested type, expected result, context
        [ TestAsset\A::class, TestAsset\Option1ForA::class, null ],
        [ TestAsset\A::class, TestAsset\Option2ForA::class, TestAsset\B::class ],
        [ TestAsset\A::class, TestAsset\Option1ForA::class, TestAsset\RequiresA::class ],
        [ TestAsset\B::class, null, TestAsset\RequiresA::class ],
        [ TestAsset\B::class, TestAsset\ExtendedB::class, TestAsset\Parameters::class ],
        [ TestAsset\A::class, TestAsset\Option1ForA::class, TestAsset\Parameters::class ],
        [ TestAsset\A::class, TestAsset\Option1ForA::class, TestAsset\Constructor\EmptyConstructor::class ],
        [ TestAsset\B::class, null, TestAsset\Constructor\EmptyConstructor::class ],

        [ TestAsset\A::class, TestAsset\Option2ForA::class, 'Some.Alias' ],
        [ TestAsset\B::class, null, 'Some.Alias' ],

        [ TestAsset\A::class, TestAsset\Option1ForA::class, 'Some.Other.Alias' ],
        [ TestAsset\B::class, TestAsset\ExtendedB::class, 'Some.Other.Alias' ],

        [ TestAsset\A::class, 'Alias.ForA', TestAsset\Constructor\OptionalArguments::class ],
        [ TestAsset\B::class, null, TestAsset\Constructor\OptionalArguments::class ],

        [ TestAsset\B::class, 'Alias.ForB', 'Alias.ForA' ]

    ],
    'preferences' => [
        TestAsset\A::class => TestAsset\Option1ForA::class
    ],
    'types' => [
        TestAsset\B::class => [
            'preferences' => [
                TestAsset\A::class => TestAsset\Option2ForA::class
            ]
        ],
        TestAsset\RequiresA::class => [
            'preferences' => [
                TestAsset\A::class => 'Invalid.Class.Name'
            ]
        ],
        TestAsset\Parameters::class => [
            'preferences' => [
                TestAsset\B::class => TestAsset\ExtendedB::class
            ]
        ],

        TestAsset\Constructor\OptionalArguments::class => [
            'preferences' => [
                TestAsset\A::class => 'Alias.ForA',
                TestAsset\B::class => 'Alias.ForA',
            ]
        ],

        'Some.Alias' => [
            'typeOf' => TestAsset\Hierarchy\A::class,
            'preferences' => [
                TestAsset\A::class => TestAsset\Option2ForA::class
            ]
        ],
        'Some.Other.Alias' => [
            'typeOf' => TestAsset\Hierarchy\A::class,
            'preferences' => [
                TestAsset\B::class => TestAsset\ExtendedB::class
            ]
        ],
        'Alias.ForA' => [
            'typeOf' => TestAsset\A::class,
            'preferences' => [
                TestAsset\B::class => 'Alias.ForB'
            ]
        ],
        'Alias.ForB' => [
            'typeOf' => TestAsset\B::class,
        ]
    ]
];
