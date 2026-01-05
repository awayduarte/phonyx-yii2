<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'api' => [
            'class' => 'backend\modules\api\Module',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        // PrettyUrl
        'urlManager' => [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'rules' => [

            //Tracks
            [
                'class' => 'yii\rest\UrlRule',
                'controller' => ['api/track'],
                'pluralize' => false,
                'extraPatterns' => [
                    'GET search' => 'search',
                    'GET latest' => 'latest',
                    'GET trending' => 'trending',
                    'POST {id}/like' => 'like',
                    'DELETE {id}/like' => 'unlike',
                ],
            ],

            /*
            * PLAYLISTS
            */
            [
                'class' => 'yii\rest\UrlRule',
                'controller' => ['api/playlist'],
                'pluralize' => false,
                'extraPatterns' => [
                    'GET {id}/tracks' => 'tracks',
                    'POST {id}/tracks/{trackId}' => 'add-track',
                    'DELETE {id}/tracks/{trackId}' => 'remove-track',
                    'PUT {id}/tracks/reorder' => 'reorder',
                ],
            ],

            /*
            * ARTISTS
            */
            [
                'class' => 'yii\rest\UrlRule',
                'controller' => ['api/artist'],
                'pluralize' => false,
                'extraPatterns' => [
                    'GET {id}/tracks' => 'tracks',
                    'GET {id}/albums' => 'albums',
                    'POST {id}/follow' => 'follow',
                    'DELETE {id}/follow' => 'unfollow',
                    'GET count' => 'count',
                ],
            ],

            /*
            * USERS
            */
            [
                'class' => 'yii\rest\UrlRule',
                'controller' => ['api/user'],
                'pluralize' => false,
                'extraPatterns' => [
                    'GET me' => 'me',
                    'PUT me' => 'update-me',
                    'GET {username}/nome' => 'nome',
                ],
            ],
        ],
    ],

        
    ],
    'params' => $params,
];
