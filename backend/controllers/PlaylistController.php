<?php

namespace backend\controllers;

use Yii;
use common\models\Playlist;
use common\models\PlaylistTrack;
use common\models\Track;
use backend\models\PlaylistSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PlaylistController implements the CRUD actions for Playlist model.
 */
class PlaylistController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'add-track' => ['POST'],
                        'remove-track' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Playlist models.
     */
    public function actionIndex()
    {
        $searchModel = new PlaylistSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Playlist model.
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Playlist model.
     */
    public function actionCreate()
    {
        $model = new Playlist();

        if ($model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Playlist model.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Playlist model.
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Shows tracks inside a playlist.
     */
    public function actionTracks($id)
    {
        $playlist = $this->findModel($id);

        $dataProvider = new ActiveDataProvider([
            'query' => PlaylistTrack::find()
                ->where(['playlist_id' => $playlist->id])
                ->with(['track.artist'])
                ->orderBy(['position' => SORT_ASC]),
            'pagination' => false,
        ]);

        // tracks that are NOT in playlist (for add dropdown)
        $availableTracks = Track::find()
            ->where([
                'not in',
                'id',
                PlaylistTrack::find()
                    ->select('track_id')
                    ->where(['playlist_id' => $playlist->id])
            ])
            ->all();

        return $this->render('tracks', [
            'playlist'        => $playlist,
            'dataProvider'    => $dataProvider,
            'availableTracks' => $availableTracks,
        ]);
    }

    /**
     * Adds a track to playlist.
     */
    public function actionAddTrack($id)
    {
        $playlist = $this->findModel($id);
        $trackId = Yii::$app->request->post('track_id');

        if (!$trackId || !Track::find()->where(['id' => $trackId])->exists()) {
            throw new NotFoundHttpException('Track not found.');
        }

        $exists = PlaylistTrack::find()
            ->where(['playlist_id' => $playlist->id, 'track_id' => $trackId])
            ->exists();

        if (!$exists) {
            $position = PlaylistTrack::find()
                ->where(['playlist_id' => $playlist->id])
                ->max('position');

            $pivot = new PlaylistTrack();
            $pivot->playlist_id = $playlist->id;
            $pivot->track_id = $trackId;
            $pivot->position = $position !== null ? $position + 1 : 1;
            $pivot->save(false);
        }

        return $this->redirect(['tracks', 'id' => $playlist->id]);
    }

    /**
     * Removes a track from playlist.
     */
    public function actionRemoveTrack($id, $track_id)
    {
        PlaylistTrack::deleteAll([
            'playlist_id' => $id,
            'track_id' => $track_id,
        ]);

        return $this->redirect(['tracks', 'id' => $id]);
    }

    /**
     * Finds the Playlist model based on its primary key value.
     */
    protected function findModel($id)
    {
        if (($model = Playlist::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
