<?php

namespace backend\modules\api\controllers;
use yii\filters\auth\QueryParamAuth;


use yii\rest\ActiveController;

/**
 * Default controller for the `api` module
 */
class ArtistController extends ActiveController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public $modelClass = 'common\models\Artist'; 

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function behaviors()
    {
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
    'class' => QueryParamAuth::className(),
    //only=> ['index'], //Apenas para o GET
    ];
    return $behaviors;
    }





    public function actionCount()
    {
    $artistsmodel = new $this->modelClass;
    $recs = $artistsmodel::find()->all();
    return ['count' => count($recs)];
    }

    public function actionNomes()
    {
    $artistsmodel = new $this->modelClass;
    $recs = $artistsmodel::find()->select(['stage_name'])->all();
    return $recs;
    }

    public function actionNome($stagename)
    {
        $modelClass = $this->modelClass;
        $rec = $modelClass::find()
            ->where(['stage_name' => $stagename])
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
