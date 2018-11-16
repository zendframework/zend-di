<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ZendTest\Di;

return [
    'expect' => [
        // Requested type, expected result, context
        'global' => [TestAsset\A::class, TestAsset\Option1ForA::class, null],
        'definedOnClass' => [TestAsset\A::class, TestAsset\Option2ForA::class, TestAsset\B::class],
        'globalFallbackOnInvalid' => [TestAsset\A::class, TestAsset\Option1ForA::class, TestAsset\RequiresA::class],
        'notDefinedParams' => [TestAsset\B::class, null, TestAsset\RequiresA::class],
        'definedSubclassOnClass' => [TestAsset\B::class, TestAsset\ExtendedB::class, TestAsset\Parameters::class],
        'globalFallbackOnUndefinedParams' => [TestAsset\A::class, TestAsset\Option1ForA::class, TestAsset\Parameters::class],
        'globalFallbackOnUndefinedClass' => [TestAsset\A::class, TestAsset\Option1ForA::class, TestAsset\Constructor\EmptyConstructor::class],
        'notDefinedClass' => [TestAsset\B::class, null, TestAsset\Constructor\EmptyConstructor::class],

        'definedOnAlias' => [TestAsset\A::class, TestAsset\Option2ForA::class, 'Some.Alias'],
        'notDefinedOnAlias' => [TestAsset\B::class, null, 'Some.Alias'],

        'globalFallbackOnAlias' => [TestAsset\A::class, TestAsset\Option1ForA::class, 'Some.Other.Alias'],
        'definedSubclassOnAlias' => [TestAsset\B::class, TestAsset\ExtendedB::class, 'Some.Other.Alias'],

        'aliasDefinedOnClass' => [TestAsset\A::class, 'Alias.ForA', TestAsset\Constructor\OptionalArguments::class],
        'invalidAliasDefinedOnClass' => [TestAsset\B::class, null, TestAsset\Constructor\OptionalArguments::class],

        'aliasDefinedOnAlias' => [TestAsset\B::class, 'Alias.ForB', 'Alias.ForA'],

    ],
    'preferences' => [
        TestAsset\A::class => TestAsset\Option1ForA::class,
    ],
    'types' => [
        TestAsset\B::class => [
            'preferences' => [
                TestAsset\A::class => TestAsset\Option2ForA::class,
            ],
        ],
        TestAsset\RequiresA::class => [
            'preferences' => [
                TestAsset\A::class => 'Invalid.Class.Name',
            ],
        ],
        TestAsset\Parameters::class => [
            'preferences' => [
                TestAsset\B::class => TestAsset\ExtendedB::class,
            ],
        ],

        TestAsset\Constructor\OptionalArguments::class => [
            'preferences' => [
                TestAsset\A::class => 'Alias.ForA',
                TestAsset\B::class => 'Alias.ForA',
            ],
        ],

        'Some.Alias' => [
            'typeOf' => TestAsset\Hierarchy\A::class,
            'preferences' => [
                TestAsset\A::class => TestAsset\Option2ForA::class,
            ],
        ],
        'Some.Other.Alias' => [
            'typeOf' => TestAsset\Hierarchy\A::class,
            'preferences' => [
                TestAsset\B::class => TestAsset\ExtendedB::class,
            ],
        ],
        'Alias.ForA' => [
            'typeOf' => TestAsset\A::class,
            'preferences' => [
                TestAsset\B::class => 'Alias.ForB',
            ],
        ],
        'Alias.ForB' => [
            'typeOf' => TestAsset\B::class,
        ],
    ],
];
