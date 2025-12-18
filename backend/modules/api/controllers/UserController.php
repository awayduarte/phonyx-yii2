<?php

namespace backend\modules\api\controllers;

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

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCount()
    {
    $usersmodel = new $this->modelClass;
    $recs = $usersmodel::find()->all();
    return ['count' => count($recs)];
    }

    public function actionNomes()
    {
    $usersmodel = new $this->modelClass;
    $recs = $usersmodel::find()->select(['username'])->all();
    return $recs;
    }

    public function actionNome($username)
    {
        $modelClass = $this->modelClass;
        $rec = $modelClass::find()
            ->where(['username' => $username])
            ->asArray()
            ->one();

        return $rec ?: ['error' => 'User not found'];
    }   
    public function actionPreco($id)
    {
    $pratosmodel = new $this->modelClass;
    //$recs = $pratosmodel::find()->select(['preco'])->where(['id' => $id])->all(); //array
    $recs = $pratosmodel::find()->select(['preco'])->where(['id' => $id])->one(); //objeto json
    return $recs;
    }
    
}
