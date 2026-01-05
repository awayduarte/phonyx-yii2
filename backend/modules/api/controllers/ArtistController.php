<?php

namespace backend\modules\api\controllers;
use yii\filters\auth\QueryParamAuth;
use common\models\Artist;
use common\models\Track;


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
    'except' => ['index', 'view', 'tracks'],
    ];
    return $behaviors;
    }

     public function actionAlbums($id)
    {
        $artist = Artist::findOne((int)$id);
        if (!$artist) {
            throw new NotFoundHttpException('Artist not found');
        }

        return Album::find()
            ->where(['artist_id' => $artist->id])
            ->orderBy(['release_date' => SORT_DESC])
            ->all();
    }


    public function actionTracks($id)
    {
        $artist = Artist::findOne((int)$id);
        if (!$artist) throw new NotFoundHttpException('Artist not found');

        return Track::find()
            ->select(['id','title','duration'])
            ->where(['artist_id' => (int)$artist->id])
            ->all();
    }

public function actionFollow($id)
    {
        $artist = Artist::findOne((int)$id);
        if (!$artist) {
            throw new NotFoundHttpException('Artist not found');
        }

        $userId = Yii::$app->user->id;

        if ($artist->user_id == $userId) {
            throw new ForbiddenHttpException('You cannot follow yourself');
        }

        $exists = Follow::findOne([
            'follower_id' => $userId,
            'artist_id' => $artist->id,
        ]);

        if ($exists) {
            return ['message' => 'Already following'];
        }

        $follow = new Follow([
            'follower_id' => $userId,
            'artist_id' => $artist->id,
        ]);

        if ($follow->save()) {
            return ['message' => 'Followed successfully'];
        }

        return $follow->errors;
    }



        public function actionUnfollow($id)
    {
        $follow = Follow::findOne([
            'follower_id' => Yii::$app->user->id,
            'artist_id' => (int)$id,
        ]);

        if (!$follow) {
            throw new NotFoundHttpException('Follow not found');
        }

        $follow->delete();
        return ['message' => 'Unfollowed successfully'];
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
