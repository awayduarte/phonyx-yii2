<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\db\Query;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use common\models\Playlist;
use common\models\PlaylistTrack;

class PlaylistController extends ActiveController
{
    public $modelClass = 'common\models\Playlist';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'my' => ['GET'],
                'tracks' => ['GET'],
                'add-track' => ['POST'],
                'remove-track' => ['DELETE', 'POST'],
                'reorder' => ['PUT', 'POST'],
                'ping' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    private function baseUrl(): string
    {
        return rtrim(Yii::$app->request->hostInfo . Yii::$app->request->baseUrl, '/');
    }

    private function toAbsUrl($path): string
    {
        if ($path === null) return '';
        $path = trim((string)$path);
        if ($path === '') return '';
        if (preg_match('~^https?://~i', $path)) return $path;
        return $this->baseUrl() . '/' . ltrim($path, '/');
    }

    private function normalizeGenre($val): string
    {
        if ($val === null) return 'Outros';

       
        if (is_string($val)) {
            $s = trim($val);
            return $s !== '' ? $s : 'Outros';
        }

       
        if (is_array($val)) {
            if (!empty($val['name'])) return (string)$val['name'];
            if (!empty($val['title'])) return (string)$val['title'];
            return 'Outros';
        }

        
        if (is_object($val)) {
            if (isset($val->name) && $val->name) return (string)$val->name;
            if (isset($val->title) && $val->title) return (string)$val->title;

           
            if (method_exists($val, '__toString')) return (string)$val;
            return 'Outros';
        }

        return 'Outros';
    }

    public function actionTracks($id)
{
    $playlist = \common\models\Playlist::findOne((int)$id);
    if (!$playlist) {
        throw new \yii\web\NotFoundHttpException('Playlist not found');
    }

    if ((int)$playlist->user_id !== (int)\Yii::$app->user->id) {
        throw new \yii\web\ForbiddenHttpException('Not allowed');
    }

    
    $rows = (new \yii\db\Query())
        ->from(['pt' => 'playlist_track'])
        ->innerJoin(['t' => 'track'], 't.id = pt.track_id')
        ->leftJoin(['g' => 'genre'], 'g.id = t.genre_id')
        ->leftJoin(['aa' => 'asset'], 'aa.id = t.audio_asset_id')
        ->leftJoin(['ca' => 'asset'], 'ca.id = t.cover_asset_id')
        ->where(['pt.playlist_id' => (int)$playlist->id])
        ->orderBy(['pt.position' => SORT_ASC, 'pt.id' => SORT_ASC])
        ->select([
            'id' => 't.id',
            'title' => 't.title',
            'duration' => 't.duration',
            'genre_name' => 'g.name',
            'genre_title' => 'g.title',
            'audio_path' => 'aa.path',
            'cover_path' => 'ca.path',
        ])
        ->all();

    $base = rtrim(\Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl, '/');

    $toAbs = function($path) use ($base) {
        if ($path === null) return '';
        $path = trim((string)$path);
        if ($path === '') return '';
        if (preg_match('~^https?://~i', $path)) return $path;
        return $base . '/' . ltrim($path, '/');
    };

    $out = [];
    foreach ($rows as $r) {
        $genre = $r['genre_name'] ?? $r['genre_title'] ?? 'Outros';
        $genre = trim((string)$genre);
        if ($genre === '') $genre = 'Outros';

        $out[] = [
            'id' => (int)($r['id'] ?? 0),
            'title' => (string)($r['title'] ?? ''),
            'duration' => (int)($r['duration'] ?? 0),
            'genre' => $genre,                // ✅ sempre string
            'audio_url' => $toAbs($r['audio_path'] ?? ''),
            'cover_url' => $toAbs($r['cover_path'] ?? ''),
        ];
    }

    return $out;
}

    public function actionMy()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = (int)Yii::$app->user->id;
        if ($userId <= 0) {
            Yii::$app->response->statusCode = 401;
            return ['ok' => false, 'error' => 'Not authenticated'];
        }

        $playlists = Playlist::find()
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $out = [];
        foreach ($playlists as $p) {
            $name = '';
            if (isset($p->title) && $p->title !== null) $name = (string)$p->title;
            else if (isset($p->name) && $p->name !== null) $name = (string)$p->name;

            $coverPath = '';
            if (isset($p->coverAsset) && $p->coverAsset && !empty($p->coverAsset->path)) {
                $coverPath = (string)$p->coverAsset->path;
            } elseif (isset($p->cover_url) && !empty($p->cover_url)) {
                $coverPath = (string)$p->cover_url;
            }

            $out[] = [
                'id' => (int)$p->id,
                'name' => $name,
                'cover_url' => $this->toAbsUrl($coverPath),
            ];
        }

        return $out;
    }

    public function actionAddTrack($id, $trackId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) throw new NotFoundHttpException('Playlist not found');

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $exists = PlaylistTrack::find()
            ->where(['playlist_id' => (int)$playlist->id, 'track_id' => (int)$trackId])
            ->exists();

        if (!$exists) {
            $pt = new PlaylistTrack();
            $pt->playlist_id = (int)$playlist->id;
            $pt->track_id = (int)$trackId;

            if ($pt->hasAttribute('position')) {
                $maxPos = (new Query())
                    ->from('playlist_track')
                    ->where(['playlist_id' => (int)$playlist->id])
                    ->max('position');
                $pt->position = ((int)$maxPos) + 1;
            }

            $pt->save(false);
        }

        return ['ok' => true];
    }

    public function actionRemoveTrack($id, $trackId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) throw new NotFoundHttpException('Playlist not found');

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }

        PlaylistTrack::deleteAll([
            'playlist_id' => (int)$playlist->id,
            'track_id' => (int)$trackId,
        ]);

        return ['ok' => true];
    }

    public function actionReorder($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) throw new NotFoundHttpException('Playlist not found');

        if ((int)$playlist->user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $items = Yii::$app->request->bodyParams;
        if (!is_array($items)) {
            Yii::$app->response->statusCode = 400;
            return ['ok' => false, 'error' => 'Invalid body'];
        }

        foreach ($items as $item) {
            $trackId = isset($item['track_id']) ? (int)$item['track_id'] : 0;
            $pos = isset($item['position']) ? (int)$item['position'] : 0;
            if ($trackId <= 0) continue;

            PlaylistTrack::updateAll(
                ['position' => $pos],
                ['playlist_id' => (int)$playlist->id, 'track_id' => $trackId]
            );
        }

        return ['ok' => true];
    }

    public function actionPing()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'ok' => true,
            'user_id' => (int)Yii::$app->user->id,
            'time' => date('c'),
        ];
    }
}
