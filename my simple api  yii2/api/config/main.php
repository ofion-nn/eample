<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'modules' => [
        'v1' => [
          'class' => 'api\modules\v1\Module',
        ],
    ],
    'components' => [
        /*'session' => [
            'cookieParams' => [
                'httpOnly' => true,
                'secure' => true,

            ]
        ],*/
        'request' => [
            'enableCsrfCookie' => false,
            'csrfCookie' => [
                'httpOnly' => true,
                'secure' => true
            ],
            'csrfParam' => '_csrf-api',
            'cookieValidationKey' => '***************',
            'enableCsrfValidation' => true,
            'enableCookieValidation' => true,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*'i18n' => [
            'translations' => [
                'vendor*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'ru-RU',
                    'basePath' => '@common/messages',
                ],
            ],
        ],*/
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                "<_m:v1>/<_a:[\w-]+>" => "<_m>/default/<_a>"
            ],
        ],
       /* 'fs' => [
            'class' => 'creocoder\flysystem\LocalFilesystem',
            'path' => '@webroot/uploads',
        ],*/
    ],
    'params' => $params,
];
