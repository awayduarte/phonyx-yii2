<?php

namespace backend\controllers;

use Yii;
use common\models\Playlist;
use common\models\Track;
use common\models\PlaylistTrack;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PlaylistTrackController handles adding and removing tracks from playlists.
 */
class PlaylistTrackController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'remove' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * List tracks in a playlist and allow adding new ones.
     *
     * @param int $id playlist id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($id)
    {
        $playlist = Playlist::findOne($id);

        if (!$playlist) {
            throw new NotFoundHttpException('Playlist not found.');
        }

        // Tracks already in playlist
        $playlistTracks = $playlist->tracks;

        // Tracks not yet in playlist
        $availableTracks = Track::find()
            ->where([
                'not in',
                'id',
                PlaylistTrack::find()
                    ->select('track_id')
                    ->where(['playlist_id' => $playlist->id])
            ])
            ->andWhere(['deleted_at' => null])
            ->all();

        return $this->render('index', [
            'playlist' => $playlist,
            'playlistTracks' => $playlistTracks,
            'availableTracks' => $availableTracks,
        ]);
    }

    /**
     * Add a track to a playlist.
     *
     * @param int $playlist_id
     * @param int $track_id
     * @return \yii\web\Response
     */
    public function actionAdd($playlist_id, $track_id)
    {
        $model = new PlaylistTrack([
            'playlist_id' => $playlist_id,
            'track_id' => $track_id,
        ]);

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Track added to playlist.');
        } else {
            Yii::$app->session->setFlash('error', 'Track already exists in playlist.');
        }

        return $this->redirect(['index', 'id' => $playlist_id]);
    }

    /**
     * Remove a track from a playlist.
     *
     * @param int $playlist_id
     * @param int $track_id
     * @return \yii\web\Response
     */
    public function actionRemove($playlist_id, $track_id)
    {
        PlaylistTrack::deleteAll([
            'playlist_id' => $playlist_id,
            'track_id' => $track_id,
        ]);

        Yii::$app->session->setFlash('success', 'Track removed from playlist.');

        return $this->redirect(['index', 'id' => $playlist_id]);
    }
}
