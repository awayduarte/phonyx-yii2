<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\Artist;
use backend\models\UserSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Transaction;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();

        $dataProvider = $searchModel->search(
            $this->request->queryParams
        );

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create';

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
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // store previous role to compare changes
        $oldRole = $model->role;

        if ($this->request->isPost && $model->load($this->request->post())) {

            // start DB transaction to keep data consistent
            $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);

            try {
                // -----------------------
                // Save user data
                // -----------------------
                if (!$model->save()) {
                    throw new \Exception('User not saved');
                }

                // -----------------------
                // Sync RBAC role
                // -----------------------
                $auth = Yii::$app->authManager;

                // remove all existing RBAC assignments for this user
                $auth->revokeAll($model->id);

                // assign new RBAC role based on user.role column
                $rbacRole = $auth->getRole($model->role);

                if ($rbacRole === null) {
                    throw new \Exception('RBAC role not found: ' . $model->role);
                }

                $auth->assign($rbacRole, $model->id);

                // -----------------------
                // Handle Artist profile
                // -----------------------

                // user became artist
                if ($oldRole !== User::ROLE_ARTIST && $model->role === User::ROLE_ARTIST) {

                    $exists = Artist::find()
                        ->where(['user_id' => $model->id])
                        ->exists();

                    if (!$exists) {
                        $artist = new Artist();
                        $artist->user_id = $model->id;
                        $artist->stage_name = $model->username;

                        if (!$artist->save()) {
                            throw new \Exception('Artist not created');
                        }
                    }
                }

                // user is no longer artist
                if ($oldRole === User::ROLE_ARTIST && $model->role !== User::ROLE_ARTIST) {
                    Artist::deleteAll(['user_id' => $model->id]);
                }

                // -----------------------
                // Force logout if user removed own admin role
                // -----------------------
                if (
                    Yii::$app->user->id === $model->id &&
                    $oldRole === User::ROLE_ADMIN &&
                    $model->role !== User::ROLE_ADMIN
                ) {
                    $transaction->commit();

                    // immediately invalidate backend session
                    Yii::$app->user->logout();

                    return $this->redirect(['/site/login']);
                }

                // commit transaction
                $transaction->commit();

                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\Throwable $e) {
                // rollback everything on error
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        // soft delete instead of physical delete
        $model = $this->findModel($id);
        $model->softDelete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne([
            'id' => $id,
            'deleted_at' => null,
        ])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
