<?php

namespace api\controllers\v1;

use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;

class TracksController extends ActiveController
{
    public $modelClass = 'common\models\Track';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index', 'view', 'search'],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => $this->modelClass::find()->select(['id','title','artist_id','duration']),
            'pagination' => ['pageSize' => 20],
        ]);
    }

    public function actionSearch($q = '')
    {
        $q = trim((string)$q);

        return new ActiveDataProvider([
            'query' => $this->modelClass::find()
                ->select(['id','title','artist_id','duration'])
                ->andFilterWhere(['like', 'title', $q]),
            'pagination' => ['pageSize' => 20],
        ]);
    }
}
