<?php

namespace console\controllers;

use yii\console\Controller;
use common\models\User;
use Yii;

class InitController extends Controller
{
    /**
     * Cria o primeiro utilizador admin
     * Uso: php yii init/admin
     */
    public function actionAdmin()
    {
        echo "A criar utilizador admin...\n";

        $user = new User();
        $user->email = 'admin@phonyx.com';
        $user->username = 'admin';
        $user->setPassword('admin123');
        $user->status = 1;

        if (!$user->save()) {
            print_r($user->errors);
            return;
        }

        $auth = Yii::$app->authManager;
        $role = $auth->getRole('admin');
        $auth->assign($role, $user->id);

        echo "Utilizador admin criado com sucesso!\n";
        echo "Email: admin@phonyx.com\n";
        echo "Password: admin123\n";
    }
}
