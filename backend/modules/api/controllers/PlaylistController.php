<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use common\models\Playlist;
use common\models\PlaylistTrack;

class PlaylistController extends ActiveController
{
    public $modelClass = 'common\models\Playlist';

    public function actions()
    {
<<<<<<< HEAD
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }


public function behaviors()
{
    $behaviors = parent::behaviors();

    $behaviors['authenticator'] = [
        'class' => \yii\filters\auth\HttpBearerAuth::class,
        'only' => ['my','tracks','add-track','remove-track','reorder','create','update','delete'],
    ];

    return $behaviors;
}

public function actionCreate()
{
    $playlist = new Playlist();
    $playlist->load(Yii::$app->request->bodyParams, '');

    // Forçar o user_id do token
    $playlist->user_id = Yii::$app->user->id;

    if ($playlist->save()) {
        return [
            'id' => $playlist->id,
            'title' => $playlist->title,
            'description' => $playlist->description,
            'user_id' => $playlist->user_id,
        ];
    }

    return [
        'error' => true,
        'messages' => $playlist->errors
    ];
}


    public function actionUpdate($id)
    {
        $playlist = Playlist::findOne((int)$id);
        $this->checkOwner($playlist);

        $playlist->load(Yii::$app->request->bodyParams, '');
        if ($playlist->save()) {
            return $playlist;
        }

        return ['error' => $playlist->errors];
    }

    public function actionDelete($id)
    {
        $playlist = Playlist::findOne((int)$id);
        $this->checkOwner($playlist);

        $playlist->delete();
        return ['success' => true];
    }


=======
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],

                'my' => ['GET'],
                'tracks' => ['GET'],
                'add-track' => ['POST'],
                'remove-track' => ['POST', 'DELETE'],
                'reorder' => ['POST'],
                'ping' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    // ✅ IMPORTANTE: substitui o create default para meter user_id e aceitar title/name
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    public function actionCreate()
    {
        $userId = (int)Yii::$app->user->id;
        if ($userId <= 0) {
            throw new ForbiddenHttpException('Not authenticated');
        }

        $data = Yii::$app->request->bodyParams;

        $model = new Playlist();

        // aceita "title" ou "name"
        if (empty($data['title']) && !empty($data['name'])) {
            $data['title'] = $data['name'];
        }
        if (empty($data['name']) && !empty($data['title'])) {
            $data['name'] = $data['title'];
        }

        // força user_id do token
        $data['user_id'] = $userId;

        $model->load($data, '');

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        // devolve 422 com erros detalhados
        Yii::$app->response->statusCode = 422;
        return [
            'message' => 'Validation failed',
            'errors' => $model->errors,
        ];
    }

>>>>>>> 6715715 (Atualização API playlists, utilizadores e módulo matemática)
    public function actionTracks($id)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist not found');
        }

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $tracks = Track::find()
            ->innerJoin('playlist_track pt', 'pt.track_id = track.id')

        $rows = (new \yii\db\Query())
            ->from(['pt' => 'playlist_track'])
            ->innerJoin(['t' => 'track'], 't.id = pt.track_id')
            ->leftJoin(['g' => 'genre'], 'g.id = t.genre_id')
            ->leftJoin(['ac' => 'asset'], 'ac.id = t.audio_asset_id')
            ->leftJoin(['cc' => 'asset'], 'cc.id = t.cover_asset_id')
            ->where(['pt.playlist_id' => (int)$playlist->id])
            ->orderBy(['pt.position' => SORT_ASC, 'pt.id' => SORT_ASC])
            ->select([
                'id' => 't.id',
                'title' => 't.title',
                'duration' => 't.duration',
                'genre' => 'COALESCE(g.name, g.title, "Outros")',
                'audio_url' => 'ac.path',
                'cover_url' => 'cc.path',
            ])
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int)($r['id'] ?? 0),
                'title' => (string)($r['title'] ?? ''),
                'duration' => (int)($r['duration'] ?? 0),
                'genre' => (string)($r['genre'] ?? 'Outros'),
                'audio_url' => $r['audio_url'] ? (string)$r['audio_url'] : '',
                'cover_url' => $r['cover_url'] ? (string)$r['cover_url'] : '',
            ];
        }

        return $out;
    }

    public function actionMy()
    {
        $userId = (int)Yii::$app->user->id;
        if ($userId <= 0) {
            Yii::$app->response->statusCode = 401;
            return ['message' => 'Not authenticated'];
        }

        $playlists = Playlist::find()
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $base = Yii::$app->request->hostInfo . Yii::$app->request->baseUrl;

        $out = [];
        foreach ($playlists as $p) {
            $cover = $p->cover_url ?? null;
            if ($cover && !preg_match('~^https?://~i', $cover)) {
                $cover = rtrim($base, '/') . '/' . ltrim($cover, '/');
            }

            $out[] = [
                'id' => (int)$p->id,
                'name' => (string)($p->name ?? $p->title ?? ''),
                'cover_url' => $cover,
            ];
        }

        return $out;
    }

    public function actionAddTrack($id, $trackId)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) throw new NotFoundHttpException('Playlist not found');

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $exists = (new \yii\db\Query())
            ->from('playlist_track')
            ->where(['playlist_id' => (int)$playlist->id, 'track_id' => (int)$trackId])
            ->exists();

        if (!$exists) {
            Yii::$app->db->createCommand()->insert('playlist_track', [
                'playlist_id' => (int)$playlist->id,
                'track_id' => (int)$trackId,
            ])->execute();
        }

        return ['ok' => true];
    }

    public function actionRemoveTrack($id, $trackId)
    {
        PlaylistTrack::deleteAll([
            'playlist_id' => (int)$id,
            'track_id' => (int)$trackId,
        ]);

        return ['ok' => true];
    }

    public function actionReorder($id)
    {
        $items = Yii::$app->request->bodyParams;

        foreach ($items as $item) {
            PlaylistTrack::updateAll(
                ['position' => (int)$item['position']],
                [
                    'playlist_id' => (int)$id,
                    'track_id' => (int)$item['track_id'],
                ]
            );
        }

        return ['ok' => true];
    }

<<<<<<< HEAD

    public function actionMy()
    {
        
        $user = Yii::$app->user->identity;       
        return Playlist::find()
            ->where(['user_id' => $user->id])
            ->all();
    }




    private function checkOwner($playlist)
=======
    public function actionPing()
>>>>>>> 6715715 (Atualização API playlists, utilizadores e módulo matemática)
    {
        return [
            'ok' => true,
            'user_id' => (int)Yii::$app->user->id,
            'time' => date('c'),
        ];
    }
}
