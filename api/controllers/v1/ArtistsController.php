<?php

namespace api\controllers\v1;

use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;
use common\models\Artist;
use common\models\Track;

class ArtistsController extends ActiveController
{
    public $modelClass = 'common\models\Artist';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index', 'view', 'tracks'],
        ];
        return $behaviors;
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
}
