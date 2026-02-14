<?php

namespace backend\modules\api\controllers;
use Yii;

use yii\rest\ActiveController;

/**
 * Default controller for the `api` module
 */
class UserController extends ActiveController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public $modelClass = 'common\models\User'; 



    /*
    public function actionCount()
    {
    $usersmodel = new $this->modelClass;
    $recs = $usersmodel::find()->all();
    return ['count' => count($recs)];
    }
    */

    public function actionMe()
    {
        $user = Yii::$app->user->identity;

        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return ['error' => 'Not authenticated'];
        }

        return [
            'id' => (int)$user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }

    
}
