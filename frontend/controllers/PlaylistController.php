<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Playlist;
use common\models\PlaylistTrack;
use common\models\Track;
use yii\web\UploadedFile;
use common\models\Asset;


class PlaylistController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['toggle-like', 'create', 'add-track', 'remove-track', 'update-cover'],


                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'toggle-like' => ['post'],
                    'add-track' => ['post'],
                    'remove-track' => ['post'],
                    'update-cover' => ['post'],
                ],

            ],
        ];
    }

    public function actionDiscover()
    {
        $userId = Yii::$app->user->isGuest ? null : Yii::$app->user->id;

        $myPlaylists = [];
        if ($userId) {
            $myPlaylists = Playlist::find()
                ->where(['user_id' => $userId])
                ->andWhere(['<>', 'title', 'Gostos'])
                ->orderBy(['updated_at' => SORT_DESC])
                ->all();
        }

        $suggestedQuery = Playlist::find()
            ->andWhere(['<>', 'title', 'Gostos'])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(12);

        if ($userId) {
            $suggestedQuery->andWhere(['<>', 'user_id', $userId]);
        }

        $suggestedPlaylists = $suggestedQuery->all();

        return $this->render('discover', [
            'myPlaylists' => $myPlaylists,
            'suggestedPlaylists' => $suggestedPlaylists,
            'userId' => $userId,
        ]);
        $coverIds = [];

        foreach ($myPlaylists as $pl) {
            if ($pl->cover_asset_id)
                $coverIds[] = $pl->cover_asset_id;
        }
        foreach ($suggestedPlaylists as $pl) {
            if ($pl->cover_asset_id)
                $coverIds[] = $pl->cover_asset_id;
        }

        $coverMap = [];
        if (!empty($coverIds)) {
            $assets = Asset::find()->where(['id' => array_unique($coverIds)])->all();
            foreach ($assets as $a) {
                $coverMap[$a->id] = $a->path;
            }
        }

        return $this->render('discover', [
            'myPlaylists' => $myPlaylists,
            'suggestedPlaylists' => $suggestedPlaylists,
            'userId' => $userId,
            'coverMap' => $coverMap,
        ]);
    }

    public function actionCreate()
    {
        $model = new Playlist();
        $model->user_id = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post())) {
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');

            if ($model->save()) {
                return $this->redirect(['playlist/discover']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionToggleLike()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;
        $trackId = (int) Yii::$app->request->post('track_id');

        if (!$trackId || !Track::find()->where(['id' => $trackId])->exists()) {
            return ['success' => false, 'message' => 'Faixa inválida.'];
        }

        $playlist = Playlist::find()
            ->where(['user_id' => $userId, 'title' => 'Gostos'])
            ->one();

        if (!$playlist) {
            $playlist = new Playlist();
            $playlist->user_id = $userId;
            $playlist->title = 'Gostos';
            $playlist->created_at = date('Y-m-d H:i:s');
            $playlist->updated_at = date('Y-m-d H:i:s');
            if (!$playlist->save(false)) {
                return ['success' => false, 'message' => 'Não foi possível criar a playlist de gostos.'];
            }
        }

        $link = PlaylistTrack::find()
            ->where(['playlist_id' => $playlist->id, 'track_id' => $trackId])
            ->one();

        if ($link) {
            $link->delete();
            return [
                'success' => true,
                'state' => 'removed',
            ];
        }

        $link = new PlaylistTrack();
        $link->playlist_id = $playlist->id;
        $link->track_id = $trackId;
        $link->created_at = time();

        if ($link->save(false)) {
            return [
                'success' => true,
                'state' => 'added',
            ];
        }

        return ['success' => false, 'message' => 'Erro ao gravar nos gostos.'];
    }
    public function actionAddTrack()
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (Yii::$app->user->isGuest) {
        return ['success' => false];
    }

    $userId = Yii::$app->user->id;
    $playlistId = (int)Yii::$app->request->post('playlist_id');
    $trackId = (int)Yii::$app->request->post('track_id');

    $playlist = Playlist::findOne(['id' => $playlistId, 'user_id' => $userId]);
    if (!$playlist) {
        return ['success' => false];
    }

    if (!Track::find()->where(['id' => $trackId])->exists()) {
        return ['success' => false];
    }

    $exists = PlaylistTrack::find()
        ->where(['playlist_id' => $playlistId, 'track_id' => $trackId])
        ->exists();

    if ($exists) {
        return ['success' => true, 'already' => true];
    }

    $maxPos = (int)PlaylistTrack::find()
        ->where(['playlist_id' => $playlistId])
        ->max('position');

    $pt = new PlaylistTrack();
    $pt->playlist_id = $playlistId;
    $pt->track_id = $trackId;
    $pt->position = $maxPos + 1;

    if ($pt->save(false)) {
        return ['success' => true];
    }

    return ['success' => false];
}


    public function actionView($id)
    {
        $playlist = Playlist::findOne((int) $id);
        if (!$playlist) {
            throw new \yii\web\NotFoundHttpException('Playlist não encontrada.');
        }

        $tracks = Track::find()
            ->innerJoin('playlist_track pt', 'pt.track_id = track.id')
            ->where(['pt.playlist_id' => $playlist->id])
            ->orderBy(['pt.position' => SORT_ASC, 'track.id' => SORT_ASC])
            ->all();


        return $this->render('view', [
            'playlist' => $playlist,
            'tracks' => $tracks,
        ]);
    }
    public function actionRemoveTrack()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $playlistId = (int) Yii::$app->request->post('playlist_id');
        $trackId = (int) Yii::$app->request->post('track_id');
        $userId = Yii::$app->user->id;

        $playlist = Playlist::findOne(['id' => $playlistId, 'user_id' => $userId]);
        if (!$playlist) {
            return ['success' => false];
        }

        PlaylistTrack::deleteAll([
            'playlist_id' => $playlistId,
            'track_id' => $trackId,
        ]);

        return ['success' => true];
    }
    public function actionUpdateCover($id)
    {
        $userId = Yii::$app->user->id;

        $playlist = Playlist::findOne(['id' => (int) $id, 'user_id' => $userId]);
        if (!$playlist) {
            throw new \yii\web\NotFoundHttpException('Playlist não encontrada.');
        }

        $file = UploadedFile::getInstanceByName('cover');
        if (!$file) {
            Yii::$app->session->setFlash('error', 'Nenhum ficheiro recebido.');
            return $this->redirect(['playlist/view', 'id' => $playlist->id]);
        }

        $ext = strtolower($file->extension);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            Yii::$app->session->setFlash('error', 'Formato inválido. Usa JPG/PNG/WEBP.');
            return $this->redirect(['playlist/view', 'id' => $playlist->id]);
        }

        $dir = Yii::getAlias('@frontend/web/uploads/playlists');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = 'playlist_' . $playlist->id . '_' . time() . '.' . $ext;
        $absolutePath = $dir . DIRECTORY_SEPARATOR . $filename;

        if (!$file->saveAs($absolutePath)) {
            Yii::$app->session->setFlash('error', 'Falhou ao guardar a imagem (permissões/pasta).');
            return $this->redirect(['playlist/view', 'id' => $playlist->id]);
        }

        $webPath = '/uploads/playlists/' . $filename;

        $asset = new Asset();
        $asset->path = $webPath;
        $asset->type = 'image';

        if (!$asset->save(false)) {
            Yii::$app->session->setFlash('error', 'Falhou a criar o registo Asset.');
            return $this->redirect(['playlist/view', 'id' => $playlist->id]);
        }

        $playlist->cover_asset_id = $asset->id;
        $playlist->save(false);

        Yii::$app->session->setFlash('success', 'Capa atualizada.');
        return $this->redirect(['playlist/view', 'id' => $playlist->id]);
    }

    public function actionSearch($q = '')
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $q = trim($q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        $tracks = \common\models\Track::find()
            ->where(['like', 'title', $q])
            ->limit(8)
            ->all();

        $out = [];
        foreach ($tracks as $t) {
            $out[] = [
                'id' => (int) $t->id,
                'title' => (string) $t->title,
                'subtitle' => (string) ($t->artist_name ?? ''),
            ];
        }

        return $out;
    }


}
