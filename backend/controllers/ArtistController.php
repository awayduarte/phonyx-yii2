<?php

namespace backend\controllers;

use common\models\Artist;
use common\models\User;
use backend\models\ArtistSearch;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ArtistController implements the CRUD actions for Artist model.
 */
class ArtistController extends Controller
{
    /**
     * @inheritDoc
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
     * Lists all Artist models.
     *
     * @return string
     */
    public function actionIndex()
    {
        // search model for filtering
        $searchModel  = new ArtistSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Artist model.
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
     * Creates a new Artist model.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Artist();

        // users that can become artists
        // active, not deleted, not already linked
        $users = User::find()
            ->where(['status' => 10])
            ->andWhere(['deleted_at' => null])
            ->andWhere([
                'not in',
                'id',
                (new Query())->select('user_id')->from('artist')
            ])
            ->all();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'users' => $users,
        ]);
    }

    /**
     * Updates an existing Artist model.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // users list for update
        // current linked user OR users not yet artists
        $users = User::find()
            ->where(['status' => 10])
            ->andWhere(['deleted_at' => null])
            ->andWhere([
                'or',
                ['id' => $model->user_id],
                [
                    'not in',
                    'id',
                    (new Query())->select('user_id')->from('artist')
                ],
            ])
            ->all();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'users' => $users,
        ]);
    }

    /**
     * Deletes an existing Artist model.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $model->softDelete();

        return $this->redirect(['index']);
    }


    /**
     * Finds the Artist model based on its primary key value.
     * @param int $id
     * @return Artist
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Artist::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
