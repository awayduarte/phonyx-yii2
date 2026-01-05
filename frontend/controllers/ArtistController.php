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
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'dashboard', 'delete-track', 'follow', 'unfollow'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'dashboard', 'delete-track', 'follow', 'unfollow'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-track' => ['post'],
                    'follow' => ['post'],
                    'unfollow' => ['post'],
                    'followers' => ['get'],
                    'following' => ['get'],
                ],
            ],
        ];
    }
    
    /**
     * Create an Artist profile
     */
    public function actionCreate()
    {
        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;

       
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
            'avatarAsset',
            'user', 
        ])
        ->where(['id' => (int)$id])
        ->one();

    if (!$artist) {
        throw new NotFoundHttpException('Artist not found.');
    }

    // --- Playlists---
    $playlists = [];
    if ($artist->user_id) {
        $plSchema = Yii::$app->db->getTableSchema('{{%playlist}}', true);
        $ownerCol = null;

        
        foreach (['user_id', 'created_by_user_id', 'owner_id', 'created_by'] as $c) {
            if ($plSchema && isset($plSchema->columns[$c])) { $ownerCol = $c; break; }
        }

        if ($ownerCol) {
            $playlists = \common\models\Playlist::find()
                ->where([$ownerCol => (int)$artist->user_id])
                ->orderBy(['id' => SORT_DESC])
                ->limit(12)
                ->all();
        }
    }

    // --- Followers---
    $followers = \common\models\User::find()
        ->innerJoin('{{%follow}} f', 'f.follower_id = {{%user}}.id')
        ->where(['f.artist_id' => (int)$artist->id])
        ->orderBy(['{{%user}}.username' => SORT_ASC])
        ->limit(24)
        ->all();

    // --- Following---
    $followingArtists = [];
    if ($artist->user_id) {
        $followingArtists = Artist::find()
            ->innerJoin('{{%follow}} f', 'f.artist_id = {{%artist}}.id')
            ->where(['f.follower_id' => (int)$artist->user_id])
            ->with(['avatarAsset', 'user'])
            ->orderBy(['{{%artist}}.stage_name' => SORT_ASC])
            ->limit(24)
            ->all();
    }

    return $this->render('view', [
        'model' => $artist,
        'playlists' => $playlists,
        'followers' => $followers,
        'followingArtists' => $followingArtists,
    ]);
}

    /**
     * Follow an artist .
     */
    public function actionFollow($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        $artistId = (int)$id;
        $userId = (int)Yii::$app->user->id;
    
        if (!$artistId || !Artist::find()->where(['id' => $artistId])->exists()) {
            return ['ok' => false, 'message' => 'Artist not found'];
        }
    
        $exists = (new \yii\db\Query())
            ->from('{{%follow}}')
            ->where(['follower_id' => $userId, 'artist_id' => $artistId])
            ->exists();
    
        if (!$exists) {
            Yii::$app->db->createCommand()->insert('{{%follow}}', [
                'follower_id' => $userId,
                'artist_id' => $artistId,
                'created_at' => new \yii\db\Expression('CURRENT_TIMESTAMP'),
            ])->execute();
        }
    
        return ['ok' => true, 'following' => true];
    }
    
    /**
     * Unfollow.
     */
    public function actionUnfollow($id)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $artistId = (int)$id;
    $userId = (int)Yii::$app->user->id;

    Yii::$app->db->createCommand()->delete('{{%follow}}', [
        'follower_id' => $userId,
        'artist_id' => $artistId,
    ])->execute();

    return ['ok' => true, 'following' => false];
}

    public function actionDeleteTrack($id)
    {
        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;

        
        $artist = $user->artist;
        if (!$artist) {
            throw new \yii\web\ForbiddenHttpException('You must be an artist to delete tracks.');
        }

        
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
    public function actionFollowers($id)
{
    $artist = Artist::findOne((int)$id);
    if (!$artist) throw new NotFoundHttpException('Artist not found.');

    $followers = \common\models\User::find()
        ->innerJoin('{{%follow}} f', 'f.follower_id = {{%user}}.id')
        ->where(['f.artist_id' => (int)$artist->id])
        ->orderBy(['{{%user}}.username' => SORT_ASC])
        ->all();

    return $this->render('followers', [
        'model' => $artist,
        'followers' => $followers,
    ]);
}

public function actionFollowing($id)
{
    $artist = Artist::findOne((int)$id);
    if (!$artist) throw new NotFoundHttpException('Artist not found.');

    $followingArtists = [];
    if ($artist->user_id) {
        $followingArtists = Artist::find()
            ->innerJoin('{{%follow}} f', 'f.artist_id = {{%artist}}.id')
            ->where(['f.follower_id' => (int)$artist->user_id])
            ->with(['avatarAsset'])
            ->orderBy(['{{%artist}}.stage_name' => SORT_ASC])
            ->all();
    }

    return $this->render('following', [
        'model' => $artist,
        'followingArtists' => $followingArtists,
    ]);
}

}