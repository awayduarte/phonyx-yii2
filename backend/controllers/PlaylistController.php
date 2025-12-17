<?php

namespace backend\controllers;

use common\models\Playlist;
use common\models\PlaylistTrack;
use backend\models\PlaylistSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

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
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Playlist models.
     *
     * @return string
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
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Shows tracks that belong to a playlist.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTracks($id)
    {
        $playlist = $this->findModel($id);

        $dataProvider = new ActiveDataProvider([
            'query' => PlaylistTrack::find()
                ->where(['playlist_id' => $playlist->id])
                ->orderBy(['position' => SORT_ASC]),
        ]);

        return $this->render('tracks', [
            'playlist'     => $playlist,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Playlist model.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Playlist();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Playlist model.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Playlist model.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Playlist model based on its primary key value.
     *
     * @param int $id
     * @return Playlist
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Playlist::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
    