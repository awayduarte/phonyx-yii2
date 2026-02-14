<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use common\models\Playlist;
use common\models\PlaylistTrack;
use common\models\Track;

class PlaylistController extends ActiveController
{
    public $modelClass = 'common\models\Playlist';

    public function actions()
    {
        $actions = parent::actions();
        // Vamos usar actions custom para create/update/delete
        unset($actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Auth por Bearer token
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'only' => [
                'my', 'tracks', 'add-track', 'remove-track', 'reorder',
                'create', 'update', 'delete', 'ping',
            ],
        ];

        // Verbos permitidos por endpoint
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

    public function actionPing()
    {
        return [
            'ok' => true,
            'user_id' => (int)Yii::$app->user->id,
            'time' => date('c'),
        ];
    }

    public function actionMy()
    {
        
        $user = Yii::$app->user->identity;       
        return Playlist::find()
            ->where(['user_id' => $user->id])
            ->all();
    }

    public function actionCreate()
    {
        $userId = (int)Yii::$app->user->id;
        if ($userId <= 0) {
            throw new ForbiddenHttpException('Não autenticado');
        }

        $data = Yii::$app->request->bodyParams;

        // Aceita "title" ou "name"
        if (empty($data['title']) && !empty($data['name'])) {
            $data['title'] = $data['name'];
        }
        if (empty($data['name']) && !empty($data['title'])) {
            $data['name'] = $data['title'];
        }

        $model = new Playlist();
        $model->load($data, '');

        // Força sempre o user_id do token
        $model->user_id = $userId;

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        Yii::$app->response->statusCode = 422;
        return [
            'message' => 'Falha de validação',
            'errors' => $model->errors,
        ];
    }

    public function actionUpdate($id)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada');
        }
        $this->checkOwner($playlist);

        $data = Yii::$app->request->bodyParams;

        // Aceita "title" ou "name"
        if (empty($data['title']) && !empty($data['name'])) {
            $data['title'] = $data['name'];
        }
        if (empty($data['name']) && !empty($data['title'])) {
            $data['name'] = $data['title'];
        }

        $playlist->load($data, '');

        if ($playlist->save()) {
            return $playlist;
        }

        Yii::$app->response->statusCode = 422;
        return [
            'message' => 'Falha de validação',
            'errors' => $playlist->errors,
        ];
    }

    public function actionDelete($id)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada');
        }
        $this->checkOwner($playlist);

        $playlist->delete();
        return ['success' => true];
    }

    public function actionTracks($id)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada');
        }
        $this->checkOwner($playlist);

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
                'position' => 'pt.position',
            ])
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int)$r['id'],
                'title' => (string)($r['title'] ?? ''),
                'duration' => (int)($r['duration'] ?? 0),
                'genre' => (string)($r['genre'] ?? 'Outros'),
                'audio_url' => $r['audio_url'] ? (string)$r['audio_url'] : '',
                'cover_url' => $r['cover_url'] ? (string)$r['cover_url'] : '',
                'position' => isset($r['position']) ? (int)$r['position'] : 0,
            ];
        }

        return $out;
    }

    public function actionAddTrack($id, $trackId)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada');
        }
        $this->checkOwner($playlist);

        $track = Track::findOne((int)$trackId);
        if (!$track) {
            throw new NotFoundHttpException('Track não encontrada');
        }

        $exists = (new \yii\db\Query())
            ->from('playlist_track')
            ->where(['playlist_id' => (int)$playlist->id, 'track_id' => (int)$trackId])
            ->exists();

        if (!$exists) {
            Yii::$app->db->createCommand()->insert('playlist_track', [
                'playlist_id' => (int)$playlist->id,
                'track_id' => (int)$trackId,
                // se tiveres position NOT NULL na BD, convém definir:
                // 'position' => (int)($this->nextPosition($playlist->id)),
            ])->execute();
        }

        return ['ok' => true];
    }

    public function actionRemoveTrack($id, $trackId)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada');
        }
        $this->checkOwner($playlist);

        PlaylistTrack::deleteAll([
            'playlist_id' => (int)$id,
            'track_id' => (int)$trackId,
        ]);

        return ['ok' => true];
    }

    public function actionReorder($id)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada');
        }
        $this->checkOwner($playlist);

        $items = Yii::$app->request->bodyParams;
        if (!is_array($items)) {
            Yii::$app->response->statusCode = 422;
            return ['message' => 'Formato inválido (esperado array)'];
        }

        foreach ($items as $item) {
            if (!isset($item['track_id'], $item['position'])) {
                continue;
            }
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

    private function checkOwner($playlist): void
    {
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada');
        }

        $uid = (int)Yii::$app->user->id;
        if ($uid <= 0) {
            throw new ForbiddenHttpException('Não autenticado');
        }

        if ((int)$playlist->user_id !== $uid) {
            throw new ForbiddenHttpException('Sem permissões');
        }
    }
}
