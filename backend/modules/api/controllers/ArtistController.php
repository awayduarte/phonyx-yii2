<?php

namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;
use common\models\Artist;
use common\models\Album;
use common\models\Track;

class ArtistController extends ActiveController
{
    public $modelClass = 'common\models\Artist';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Autenticação por Bearer Token
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index', 'view', 'tracks', 'albums'],
        ];

        return $behaviors;
    }

    public function actionAlbums($id)
    {
        $artist = Artist::findOne($id);
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
        $artist = Artist::findOne($id);
        if (!$artist) {
            throw new NotFoundHttpException('Artist not found');
        }

        return Track::find()
            ->where(['artist_id' => $artist->id])
            ->all();
    }
}
