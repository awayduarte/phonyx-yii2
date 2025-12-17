<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // allow login and error for anyone
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    // allow logout for any authenticated user
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // allow backend access only for admin
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $stats = [
            'users'     => \common\models\User::find()->where(['deleted_at' => null])->count(),
            'artists'   => \common\models\Artist::find()->count(),
            'tracks'    => \common\models\Track::find()->count(),
            'albums'    => \common\models\Album::find()->count(),
            'playlists' => \common\models\Playlist::find()->count(),
            'genres'    => \common\models\Genre::find()->count(),
            'assets'    => \common\models\Asset::find()->count(),
        ];

        return $this->render('index', [
            'stats' => $stats,
        ]);
    }


    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        // already logged in
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'login';
        $model = new \common\models\LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {

            // admin can access backend
            if (Yii::$app->user->identity->role === 'admin') {
                return $this->goHome();
            }

            // non-admin users -> logout and redirect to frontend
            Yii::$app->user->logout();

            return $this->redirect(Yii::$app->params['frontendUrl'] . '/site/login');
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
