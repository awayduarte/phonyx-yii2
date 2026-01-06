<?php
return [
    'id' => 'app-frontend-tests',
    'components' => [
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'request' => [
            'cookieValidationKey' => 'test',
        ],
        'mailer' => [
            'messageClass' => \yii\symfonymailer\Message::class
        ],
        'db' => [ 'class' => 'yii\db\Connection', 'dsn' => 'mysql:host=localhost;dbname=phonyx', 'username' => 'root', 'password' => '', 'charset' => 'utf8', ],
    ],
];
