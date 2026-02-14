<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;

class UserController extends ActiveController
{
    public $modelClass = 'common\models\User';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'me' => ['GET'],
                'update-me' => ['PUT', 'PATCH'],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        // desliga CRUD automático (index/view/create/update/delete)
        // para não expores users todos na API
        return [];
    }

    public function actionMe()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return ['error' => 'Not authenticated'];
        }

        return [
            'id' => (int)$user->id,
            'username' => (string)$user->username,
            'email' => (string)$user->email,
        ];
    }

    public function actionUpdateMe()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return ['error' => 'Not authenticated'];
        }

        $body = Yii::$app->request->bodyParams;

        // campos permitidos
        $username = isset($body['username']) ? trim((string)$body['username']) : null;
        $email    = isset($body['email']) ? trim((string)$body['email']) : null;
        $password = isset($body['password']) ? (string)$body['password'] : null;

        if ($username !== null && $username !== '') {
            $user->username = $username;
        }

        if ($email !== null && $email !== '') {
            $user->email = $email;
        }

        // password é opcional (só muda se vier preenchido)
        if ($password !== null && trim($password) !== '') {
            // assume que o teu User model tem setPassword()
            $user->setPassword($password);
            $user->generateAuthKey();
        }

        // segurança básica: não deixar mudar para role/admin etc (não tocamos nisso)
        if (!$user->save()) {
            Yii::$app->response->statusCode = 422;
            return [
                'error' => 'Validation failed',
                'details' => $user->getErrors(),
            ];
        }

        return [
            'ok' => true,
            'id' => (int)$user->id,
            'username' => (string)$user->username,
            'email' => (string)$user->email,
        ];
    }
}
