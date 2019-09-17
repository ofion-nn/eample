<?php
return [
//    'name' => '',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'sourceLanguage' => 'ru',
    'language' => 'ru',
    'modules' => [
        'languages' => [
            'class' => 'common\modules\languages\Module',
            //Языки используемые в приложении
            'languages' => [
                'Русский' => 'ru',
            ],
            'default_language' => 'ru', //основной язык (по-умолчанию)
            'show_default' => false, //true - показывать в URL основной язык, false - нет
        ],
        'filesAttacher' => [
            'class' => 'mix8872\filesAttacher\Module',
            'parameters' => [
                'imageResize' => [
                    'sm' => ['width' => '320', 'height' => '180'],
                    'sm_retina' => ['width' => '640', 'height' => '360']
                ],
                'sizesNameBy' => 'key',
                'imgProcessDriver' => 'gd',
            ],
        ],
        'slider' => [
            'class' => 'common\modules\slider\Module',
        ],
        'catalog' => [
            'class' => 'common\modules\catalog\Module',
        ],
        'form' => [
            'class' => 'common\modules\form\Module',
        ],
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
            // enter optional module parameters below - only if you need to
            // use your own export download action or custom translation
            // message source
            // 'downloadAction' => 'gridview/export/download',
            // 'i18n' => []
        ],
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'config' => [
            'class' => 'mix8872\admin\components\Config'
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'assetManager' => [
            'linkAssets' => true
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'mix8872\admin\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity-backend',
                'httpOnly' => true
            ],
        ],

        'formatter' => [
            'class'           => 'yii\i18n\Formatter',
            'defaultTimeZone' => 'Europe/Moscow',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                '/' => 'site/index',
                '<controller:[a-z\-]{1,50}>/<action:[a-z\-]{1,50}>' => '<controller>/<action>',
                '<controller:[a-z\-]{1,50}>/<action:[a-z\-]{1,50}>/<param:[1-9][0-9]{0,10}>' => '<controller>/<action>'
            ],
        ],
    ],
];
