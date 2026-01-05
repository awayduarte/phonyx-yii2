<?php

namespace api\controllers\v1;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use common\models\User;

class AuthController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $body = Yii::$app->request->bodyParams;

        $login    = trim((string)($body['username'] ?? ''));
        $password = (string)($body['password'] ?? '');

        if ($login === '' || $password === '') {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'Missing credentials'];
        }

        $user = User::find()
            ->where(['username' => $login])
            ->orWhere(['email' => $login])
            ->one();

        if (!$user || !$user->validatePassword($password)) {
            Yii::$app->response->statusCode = 401;
            return ['error' => 'Invalid credentials'];
        }

        if (empty($user->access_token)) {
            $user->access_token = Yii::$app->security->generateRandomString(64);
            $user->save(false, ['access_token']);
        }

        return [
            'token' => $user->access_token,
            'user' => [
                'id' => (int)$user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ];
    }
}
