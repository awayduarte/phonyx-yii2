<?php

namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;

use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use Yii;

use common\models\Track;
use common\models\Like;
use common\models\Artist;

class TrackController extends ActiveController
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
        
        unset(
            $actions['index'],
            $actions['create'],
            $actions['update'],
            $actions['delete']); 
            
        return $actions;
    }

    public function actionIndex()
    {
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return new ActiveDataProvider([
            'query' => $this->modelClass::find()->select(['id','title','artist_id','duration','album_id','genre_id','audio_asset_id']),
            'pagination' => ['pageSize' => 20],
        ]);
    }

     public function actionView($id)
    {
        $track = Track::find()
            ->with(['artist', 'album', 'genre'])
            ->where(['id' => (int)$id])
            ->one();

        if (!$track) {
            throw new NotFoundHttpException('Track not found');
        }

        return [
            'id' => $track->id,
            'title' => $track->title,
            'artist_id' => $track->artist_id,
            'duration' => $track->duration,
            'audio_url' => $track->audioUrl, // <-- AQUI ESTÁ A MÚSICA
            'cover_url' => $track->coverUrl,
        ];
    }



    public function actionSearch($q = '')
    {
        $q = trim((string)$q);

return new ActiveDataProvider([
    'query' => $this->modelClass::find()
        ->select(['id','title','artist_id','duration'])
        ->andFilterWhere(['like', 'title', $q])
        ->with(['audioAsset']), // <-- importante
    'pagination' => ['pageSize' => 20],
]);

    }


    public function actionLatest()
    {
        return new ActiveDataProvider([
            'query' => Track::find()
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
    }


        public function actionTrending()
    {
        return Track::find()
            ->select(['track.*', 'COUNT(l.user_id) AS likes'])
            ->joinWith('likes l', false)
            ->groupBy('track.id')
            ->orderBy(['likes' => SORT_DESC])
            ->limit(20)
            ->all();
    }


     public function actionLike($id)
    {
        $userId = Yii::$app->user->id;

        if (!Track::findOne($id)) {
            throw new NotFoundHttpException('Track not found');
        }

        if (Like::findOne(['user_id' => $userId, 'track_id' => $id])) {
            return ['message' => 'Already liked'];
        }

        $like = new Like([
            'user_id' => $userId,
            'track_id' => $id,
        ]);

        $like->save();
        return ['message' => 'Track liked'];
    }


        public function actionUnlike($id)
    {
        $like = Like::findOne([
            'user_id' => Yii::$app->user->id,
            'track_id' => $id,
        ]);

        if (!$like) {
            throw new NotFoundHttpException('Like not found');
        }

        $like->delete();
        return ['message' => 'Like removed'];
    }

    

}
