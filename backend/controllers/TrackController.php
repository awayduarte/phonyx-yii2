<?php

namespace backend\controllers;

use Yii;
use common\models\Track;
use common\models\Asset;
use backend\models\TrackSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * TrackController implements the CRUD actions for Track model.
 */
class TrackController extends Controller
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
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Track models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TrackSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Track model.
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
     * Creates a new Track model.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Track();

        if ($model->load(Yii::$app->request->post())) {

            // get uploaded audio file
            $model->audioFile = UploadedFile::getInstance($model, 'audioFile');

            if (!$model->audioFile) {
                $model->addError('audioFile', 'Audio file is required.');
                return $this->render('create', ['model' => $model]);
            }

            // create asset
            $asset = new Asset();
            $asset->file = $model->audioFile;

            if (!$asset->save()) {
                $model->addError('audioFile', 'Failed to upload audio.');
                return $this->render('create', ['model' => $model]);
            }

            // link asset to track
            $model->audio_asset_id = $asset->id;

            // TODO: calculate duration from audio file
            // $model->duration = ...

            if ($model->save(false)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Track model.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Track model.
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
     * Finds the Track model based on its primary key value.
     *
     * @param int $id
     * @return Track
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Track::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
