<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language' => 'es',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache'
        ],
        'helpers' => [
            'class' => 'common\components\Helpers'
        ],
        'phpMQTT' => [
            'class' => 'common\components\phpMQTT'
        ],
        'i18n' => [
            'translations' => [
                'frontend*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages'
                ],
                'backend*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages'
                ]
            ]
        ]
    ]
];
