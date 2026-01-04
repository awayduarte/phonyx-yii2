<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'phonyx-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'defaultRoute' => 'v1/site/ping',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'CHANGE_THIS_TO_RANDOM',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'GET v1/ping' => 'v1/site/ping',

                'POST v1/auth/login' => 'v1/auth/login',

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'v1/tracks',
                        'v1/playlists',
                        'v1/artists',
                    ]
                ],

                'GET v1/tracks/search' => 'v1/tracks/search',

                'GET v1/playlists/<id:\d+>/tracks' => 'v1/playlists/tracks',
                'POST v1/playlists/<id:\d+>/tracks/<trackId:\d+>' => 'v1/playlists/add-track',
                'DELETE v1/playlists/<id:\d+>/tracks/<trackId:\d+>' => 'v1/playlists/remove-track',

                'GET v1/artists/<id:\d+>/tracks' => 'v1/artists/tracks',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
