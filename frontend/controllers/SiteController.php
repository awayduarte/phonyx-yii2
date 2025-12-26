<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use common\models\LoginForm;
use frontend\models\SignupForm;

// SEARCH MODELS
use common\models\Track;
use common\models\Artist;
use common\models\Album;
use common\models\Playlist;
use common\models\User;

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
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
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
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Homepage
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 🔍 SEARCH PAGE
     * URL: /site/search?q=...
     */
    public function actionSearch($q = null, $type = 'all')
    {
        $q = trim((string) $q);
        $type = $type ?: 'all';

        // Empty results structure
        $results = [
            'tracks'    => [],
            'artists'   => [],
            'albums'    => [],
            'playlists' => [],
            'profiles'  => [],
        ];

        // If query is empty, just show the page
        if ($q === '') {
            return $this->render('search', [
                'q' => $q,
                'type' => $type,
                'results' => $results,
            ]);
        }

        // 🎵 Tracks
        if ($type === 'all' || $type === 'songs') {
            $results['tracks'] = Track::find()
                ->joinWith(['artist', 'audioAsset'])
                ->andFilterWhere(['like', 'track.title', $q])
                ->orderBy(['track.created_at' => SORT_DESC])
                ->limit(20)
                ->all();
        }

        // 🎤 Artists
        if ($type === 'all' || $type === 'artists') {
            $results['artists'] = Artist::find()
                ->andFilterWhere(['like', 'stage_name', $q])
                ->orderBy(['stage_name' => SORT_ASC])
                ->limit(20)
                ->all();
        }

        // 💿 Albums
        if ($type === 'all' || $type === 'albums') {
            $results['albums'] = Album::find()
                ->andFilterWhere(['like', 'title', $q])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(20)
                ->all();
        }

        // 📂 Playlists
        if ($type === 'all' || $type === 'playlists') {
            $results['playlists'] = Playlist::find()
                ->andFilterWhere(['like', 'title', $q])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(20)
                ->all();
        }

        // 👤 Profiles (users)
        if ($type === 'all' || $type === 'profiles') {
            $results['profiles'] = User::find()
                ->andFilterWhere(['like', 'username', $q])
                ->orderBy(['username' => SORT_ASC])
                ->limit(20)
                ->all();
        }

        return $this->render('search', [
            'q' => $q,
            'type' => $type,
            'results' => $results,
        ]);
    }

    /**
     * Signup
     */
    public function actionSignup()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post()) && ($user = $model->signup())) {
            Yii::$app->user->login($user);
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Login
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goHome();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * About page
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
