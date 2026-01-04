<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Track;


use common\models\Artist;

class ArtistController extends Controller
{
    public function behaviors()
    {
        return [
            // Access rules
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'only' => ['create', 'dashboard', 'delete-track'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // logged-in users only
                    ],
                ],
            ],

            // HTTP verb filters (must be a separate behavior)
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'delete-track' => ['post'],
                ],
            ],
        ];
    }


    /**
     * Create an Artist profile for the currently logged-in user.
     */
    public function actionCreate()
    {
        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;

        // If the user already has an artist profile, redirect to dashboard
        if ($user->artist) {
            return $this->redirect(['dashboard']);
        }

        $model = new Artist();
        $model->user_id = $user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Artist profile created successfully!');
            return $this->redirect(['dashboard']);
        }

        return $this->render('create', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    public function actionDashboard()
    {
        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;


        $artist = $user->artist;


        if (!$artist) {
            return $this->redirect(['create']);
        }


        $tracks = \common\models\Track::find()
            ->where(['artist_id' => $artist->id])
            ->andWhere(['deleted_at' => null])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $albums = \common\models\Album::find()
            ->where(['artist_id' => $artist->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();


        return $this->render('dashboard', [
            'artist' => $artist,
            'user' => $user,
            'tracks' => $tracks,
            'albums' => $albums,
            'model' => $artist,
        ]);
    }

    public function actionView($id)
    {
        $artist = Artist::find()
            ->with([
                'tracks',
                'albums',
                'avatarAsset', // important: avatar is stored via avatar_asset_id -> asset
            ])
            ->where(['id' => (int) $id])
            ->one();

        if (!$artist) {
            throw new NotFoundHttpException('Artist not found.');
        }

        return $this->render('view', [
            'model' => $artist,
        ]);
    }
    /**
     * Follow an artist (AJAX).
     */
    public function actionFollow($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['ok' => false, 'message' => 'Login required'];
        }

        $artistId = (int) $id;
        $userId = (int) Yii::$app->user->id;

        $exists = (new \yii\db\Query())
            ->from('follow')
            ->where(['follower_id' => $userId, 'artist_id' => $artistId])
            ->exists();

        if (!$exists) {
            Yii::$app->db->createCommand()->insert('follow', [
                'follower_id' => $userId,
                'artist_id' => $artistId,
                'created_at' => new \yii\db\Expression('CURRENT_TIMESTAMP'),
            ])->execute();
        }

        return ['ok' => true, 'following' => true];
    }

    /**
     * Unfollow an artist (AJAX).
     */
    public function actionUnfollow($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['ok' => false, 'message' => 'Login required'];
        }

        $artistId = (int) $id;
        $userId = (int) Yii::$app->user->id;

        Yii::$app->db->createCommand()->delete('follow', [
            'follower_id' => $userId,
            'artist_id' => $artistId,
        ])->execute();

        return ['ok' => true, 'following' => false];
    }
    public function actionDeleteTrack($id)
    {
        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;

        // Make sure the user has an artist profile
        $artist = $user->artist;
        if (!$artist) {
            throw new \yii\web\ForbiddenHttpException('You must be an artist to delete tracks.');
        }

        // Find the track and ensure it belongs to the logged-in artist
        $track = Track::find()
            ->where(['id' => (int) $id, 'artist_id' => $artist->id])
            ->andWhere(['deleted_at' => null])
            ->one();

        if (!$track) {
            throw new NotFoundHttpException('Track not found or you do not have permission.');
        }

        // Soft delete
        $track->softDelete();

        Yii::$app->session->setFlash('success', 'Track deleted successfully.');
        return $this->redirect(['dashboard']);
    }
    public function actionEdit()
    {
        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;
        $artist = $user->artist;

        if (!$artist) {
            throw new NotFoundHttpException('Artist profile not found.');
        }

        if ($artist->load(Yii::$app->request->post()) && $artist->save()) {
            Yii::$app->session->setFlash('success', 'Artist profile updated successfully.');
            return $this->redirect(['dashboard']);
        }

        return $this->render('edit', [
            'model'  => $artist,
            'artist' => $artist,
        ]);


    }
}