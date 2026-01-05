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

    /*
     🔍 SEARCH PAGE
     */
    public function actionSearch(string $q = '', string $type = 'all')
{
    $q = trim(Yii::$app->request->get('q', $q));
    $type = Yii::$app->request->get('type', $type) ?: 'all';

    $valid = ['all','playlists','songs','artists','profiles','albums'];
    if (!in_array($type, $valid, true)) $type = 'all';

    $results = [
        'tracks' => [],
        'playlists' => [],
        'artists' => [],
        'profiles' => [],
        'albums' => [],
    ];

    if ($q === '') {
        return $this->render('search', [
            'q' => $q,
            'type' => $type,
            'results' => $results,
        ]);
    }

    // ARTISTS
    $artists = \common\models\Artist::find()
        ->where(['like', 'stage_name', $q])
        ->orWhere(['like', 'bio', $q])
        ->with(['user.profileAsset'])
        ->limit(20)
        ->all();

    $artistIds = \yii\helpers\ArrayHelper::getColumn($artists, 'id');

    // TRACKS
    $trackTable  = \common\models\Track::tableName();
    $trackSchema = \common\models\Track::getTableSchema();
    $hasFeat     = $trackSchema && isset($trackSchema->columns['feat']);
    $hasArtistId = $trackSchema && isset($trackSchema->columns['artist_id']);

    $tracksQuery = \common\models\Track::find()
        ->with([
            'audioAsset',
            'album.coverAsset',
            'artist.user.profileAsset',
        ])
        ->limit(50);

    $tracksQuery->where(['like', $trackTable . '.title', $q]);

    if ($hasFeat) {
        $tracksQuery->orWhere(['like', $trackTable . '.feat', $q]);
    }

    if ($hasArtistId && !empty($artistIds)) {
        $tracksQuery->orWhere([$trackTable . '.artist_id' => $artistIds]);
    } else {
        try {
            $tracksQuery->joinWith(['artist a'], false);
            $tracksQuery->orWhere(['like', 'a.stage_name', $q]);
        } catch (\Throwable $e) {}
    }

    $tracks = $tracksQuery->all();

    // ALBUMS
    $albums = \common\models\Album::find()
        ->andWhere(['like', 'title', $q])
        ->with(['coverAsset', 'artist'])
        ->limit(20)
        ->all();

    // PLAYLISTS (sem owner)
    $playlists = \common\models\Playlist::find()
        ->andWhere(['like', 'title', $q])
        ->with(['coverAsset'])
        ->limit(20)
        ->all();

    // PROFILES (sem display_name se não existir)
    $userTable  = \common\models\User::tableName();
    $userSchema = \common\models\User::getTableSchema();
    $hasDisplayName = $userSchema && isset($userSchema->columns['display_name']);

    $profilesQuery = \common\models\User::find()
        ->with(['profileAsset'])
        ->limit(20);

    if ($hasDisplayName) {
        $profilesQuery->andWhere(['or',
            ['like', $userTable . '.username', $q],
            ['like', $userTable . '.display_name', $q],
            ['like', $userTable . '.email', $q],
        ]);
    } else {
        $profilesQuery->andWhere(['or',
            ['like', $userTable . '.username', $q],
            ['like', $userTable . '.email', $q],
        ]);
    }

    $profiles = $profilesQuery->all();

    // TAB FILTER
    if ($type === 'artists') {
        $results['artists'] = $artists;
    } elseif ($type === 'songs') {
        $results['tracks'] = $tracks;
    } elseif ($type === 'albums') {
        $results['albums'] = $albums;
    } elseif ($type === 'playlists') {
        $results['playlists'] = $playlists;
    } elseif ($type === 'profiles') {
        $results['profiles'] = $profiles;
    } else {
        $results['artists'] = $artists;
        $results['tracks'] = $tracks;
        $results['albums'] = $albums;
        $results['playlists'] = $playlists;
        $results['profiles'] = $profiles;
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
