<?php

namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use common\models\Playlist;
use common\models\PlaylistTrack;
use common\models\Track;

class PlaylistController extends ActiveController
{
    public $modelClass = 'common\models\Playlist';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = ['class' => HttpBearerAuth::class];
        return $behaviors;
    }

    public function actionTracks($id)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) throw new NotFoundHttpException('Playlist not found');

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $tracks = Track::find()
            ->select(['track.id','track.title','track.artist_id','track.duration'])
            ->innerJoin('playlist_track pt', 'pt.track_id = track.id')
            ->where(['pt.playlist_id' => (int)$playlist->id])
            ->all();

        return ['playlist_id' => (int)$playlist->id, 'tracks' => $tracks];
    }

    public function actionAddTrack($id, $trackId)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) throw new NotFoundHttpException('Playlist not found');

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $track = Track::findOne((int)$trackId);
        if (!$track) throw new NotFoundHttpException('Track not found');

        $exists = PlaylistTrack::find()
            ->where(['playlist_id' => (int)$playlist->id, 'track_id' => (int)$track->id])
            ->exists();

        if (!$exists) {
            $pt = new PlaylistTrack();
            $pt->playlist_id = (int)$playlist->id;
            $pt->track_id = (int)$track->id;
            $pt->save(false);
            $playlist->notifyAddTrack($track->id);
        }

        return ['ok' => true];
    }

    public function actionRemoveTrack($id, $trackId)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) throw new NotFoundHttpException('Playlist not found');

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }

        PlaylistTrack::deleteAll([
            'playlist_id' => (int)$playlist->id,
            'track_id' => (int)$trackId,
        ]);
        $playlist->notifyRemoveTrack($trackId);


        return ['ok' => true];
    }

     public function actionReorder($id)
    {
        $playlist = Playlist::findOne((int)$id);
        $this->checkOwner($playlist);

        $items = Yii::$app->request->bodyParams;

        foreach ($items as $item) {
            PlaylistTrack::updateAll(
                ['position' => (int)$item['position']],
                [
                    'playlist_id' => $playlist->id,
                    'track_id' => (int)$item['track_id'],
                ]
            );
        }
        $playlist->notifyReorder();

        return ['message' => 'Playlist reordered'];
    }




    private function checkOwner($playlist)
    {
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist not found');
        }

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }
    }


}
