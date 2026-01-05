<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\db\Expression;

use common\models\Playlist;
use common\models\PlaylistTrack;
use common\models\Track;
use common\models\Asset;

class PlaylistController extends Controller
{
    public function behaviors()
    {
        return [
            // Logged users only for these actions
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'toggle-like',
                    'create',
                    'add-track',
                    'remove-track',
                    'update-cover',
                    'my-playlists',
                    'like',
                    'unlike',
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

            // Verb rules
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'toggle-like'  => ['POST'],
                    'add-track'    => ['POST'],
                    'remove-track' => ['POST'],
                    'update-cover' => ['POST'],
                    'my-playlists' => ['GET'],
                    'like'         => ['POST'],
                    'unlike'       => ['POST'],
                ],
            ],
        ];
    }

    public function actionDiscover()
{
    $userId = Yii::$app->user->isGuest ? null : (int)Yii::$app->user->id;

    // My playlists
    $myPlaylists = [];
    if ($userId) {
        $myPlaylists = Playlist::find()
            ->where(['user_id' => $userId])
            ->andWhere(['<>', 'title', 'Gostos'])
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();
    }

    // Suggested playlists
    $suggestedQuery = Playlist::find()
        ->andWhere(['<>', 'title', 'Gostos'])
        ->orderBy(['updated_at' => SORT_DESC])
        ->limit(12);

    if ($userId) {
        $suggestedQuery->andWhere(['<>', 'user_id', $userId]);
    }

    $suggestedPlaylists = $suggestedQuery->all();

    // Liked playlists 
    $likedPlaylists = [];
    if ($userId) {
        $likedPlaylists = Playlist::find()
            ->innerJoin('{{%playlist_like}} pl', 'pl.playlist_id = playlist.id')
            ->where(['pl.user_id' => $userId])
            ->andWhere(['<>', 'playlist.title', 'Gostos'])
            ->orderBy(['pl.created_at' => SORT_DESC])
            ->limit(12)
            ->all();
    }

   
    $coverIds = [];

    foreach ($myPlaylists as $pl) {
        if ($pl->cover_asset_id) $coverIds[] = $pl->cover_asset_id;
    }
    foreach ($suggestedPlaylists as $pl) {
        if ($pl->cover_asset_id) $coverIds[] = $pl->cover_asset_id;
    }
    foreach ($likedPlaylists as $pl) {
        if ($pl->cover_asset_id) $coverIds[] = $pl->cover_asset_id;
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
        'likedPlaylists' => $likedPlaylists, // ✅ NEW
        'suggestedPlaylists' => $suggestedPlaylists,
        'userId' => $userId,
        'coverMap' => $coverMap,
    ]);
}


    public function actionCreate()
    {
        $model = new Playlist();

        // Tracks to show in create form
        $availableTracks = Track::find()
            ->with(['artist'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(200)
            ->all();

        $uid = (int)Yii::$app->user->id;

        // Try to set owner field based on existing columns
        $schema = Playlist::getTableSchema();
        if ($schema) {
            foreach (['user_id', 'created_by_user_id', 'created_by', 'owner_id'] as $attr) {
                if (isset($schema->columns[$attr]) && $model->hasAttribute($attr)) {
                    $model->$attr = $uid;
                    break;
                }
            }

            // Default public if column exists
            foreach (['is_public', 'public'] as $attr) {
                if (isset($schema->columns[$attr]) && $model->hasAttribute($attr) && $model->$attr === null) {
                    $model->$attr = 1;
                }
            }
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            // Track ids selected (max 20)
            $trackIds = $post['track_ids'] ?? [];
            if (!is_array($trackIds)) $trackIds = [];
            $trackIds = array_slice(array_values(array_unique(array_map('intval', $trackIds))), 0, 20);

            if ($model->load($post)) {

                // Ensure owner id is set
                if ($schema) {
                    foreach (['user_id', 'created_by_user_id', 'created_by', 'owner_id'] as $attr) {
                        if (isset($schema->columns[$attr]) && $model->hasAttribute($attr) && empty($model->$attr)) {
                            $model->$attr = $uid;
                            break;
                        }
                    }
                }

                if ($model->save()) {

                    // Insert playlist tracks
                    $pos = 1;
                    foreach ($trackIds as $tid) {
                        if (!Track::find()->where(['id' => $tid])->exists()) continue;

                        $exists = PlaylistTrack::find()
                            ->where(['playlist_id' => $model->id, 'track_id' => $tid])
                            ->exists();

                        if ($exists) continue;

                        $pivot = new PlaylistTrack();
                        $pivot->playlist_id = (int)$model->id;
                        $pivot->track_id = (int)$tid;

                        if ($pivot->hasAttribute('position')) {
                            $pivot->position = $pos;
                        }

                        $pivot->save(false);
                        $pos++;
                    }

                    Yii::$app->session->setFlash('success', 'Playlist criada!');
                    return $this->redirect(['playlist/view', 'id' => $model->id]);
                }

                Yii::error(['playlist_create_errors' => $model->errors], __METHOD__);
                Yii::$app->session->setFlash('error', 'Não foi possível criar a playlist. Vê os erros no formulário.');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'availableTracks' => $availableTracks,
        ]);
    }

    /**
     * Like/unlike track to "Gostos" playlist (this is track-like, keep it)
     */
    public function actionToggleLike()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = (int)Yii::$app->user->id;
        $trackId = (int)Yii::$app->request->post('track_id');

        if (!$trackId || !Track::find()->where(['id' => $trackId])->exists()) {
            return ['success' => false, 'message' => 'Faixa inválida.'];
        }

        // Find or create the "Gostos" playlist
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
            ->where(['playlist_id' => (int)$playlist->id, 'track_id' => $trackId])
            ->one();

        if ($link) {
            $link->delete();
            return ['success' => true, 'state' => 'removed'];
        }

        $link = new PlaylistTrack();
        $link->playlist_id = (int)$playlist->id;
        $link->track_id = (int)$trackId;

        // Some schemas store created_at as int, others as datetime
        if ($link->hasAttribute('created_at')) {
            $link->created_at = time();
        }

        if ($link->save(false)) {
            return ['success' => true, 'state' => 'added'];
        }

        return ['success' => false, 'message' => 'Erro ao gravar nos gostos.'];
    }

    /**
     * Add track to playlist (AJAX)
     */
    public function actionAddTrack()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = (int)Yii::$app->user->id;
        $playlistId = (int)Yii::$app->request->post('playlist_id');
        $trackId = (int)Yii::$app->request->post('track_id');

        $playlist = Playlist::findOne(['id' => $playlistId, 'user_id' => $userId]);
        if (!$playlist) return ['success' => false];

        if (!Track::find()->where(['id' => $trackId])->exists()) return ['success' => false];

        $exists = PlaylistTrack::find()
            ->where(['playlist_id' => $playlistId, 'track_id' => $trackId])
            ->exists();

        if ($exists) return ['success' => true, 'already' => true];

        $maxPos = (int)PlaylistTrack::find()
            ->where(['playlist_id' => $playlistId])
            ->max('position');

        $pt = new PlaylistTrack();
        $pt->playlist_id = $playlistId;
        $pt->track_id = $trackId;

        if ($pt->hasAttribute('position')) {
            $pt->position = $maxPos + 1;
        }

        if ($pt->save(false)) return ['success' => true];

        return ['success' => false];
    }

    public function actionView($id)
    {
        $playlist = Playlist::findOne((int)$id);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada.');
        }

        $tracks = Track::find()
            ->innerJoin('playlist_track pt', 'pt.track_id = track.id')
            ->where(['pt.playlist_id' => (int)$playlist->id])
            ->orderBy(['pt.position' => SORT_ASC, 'track.id' => SORT_ASC])
            ->all();

        return $this->render('view', [
            'playlist' => $playlist,
            'tracks' => $tracks,
        ]);
    }

    /**
     * Remove track from playlist (AJAX)
     */
    public function actionRemoveTrack()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $playlistId = (int)Yii::$app->request->post('playlist_id');
        $trackId = (int)Yii::$app->request->post('track_id');
        $userId = (int)Yii::$app->user->id;

        $playlist = Playlist::findOne(['id' => $playlistId, 'user_id' => $userId]);
        if (!$playlist) return ['success' => false];

        PlaylistTrack::deleteAll([
            'playlist_id' => $playlistId,
            'track_id' => $trackId,
        ]);

        return ['success' => true];
    }

    /**
     * Update playlist cover (POST file)
     */
    public function actionUpdateCover($id)
    {
        $userId = (int)Yii::$app->user->id;

        $playlist = Playlist::findOne(['id' => (int)$id, 'user_id' => $userId]);
        if (!$playlist) {
            throw new NotFoundHttpException('Playlist não encontrada.');
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
        if (!is_dir($dir)) mkdir($dir, 0777, true);

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

        $playlist->cover_asset_id = (int)$asset->id;
        $playlist->save(false);

        Yii::$app->session->setFlash('success', 'Capa atualizada.');
        return $this->redirect(['playlist/view', 'id' => $playlist->id]);
    }

    /**
     * Search tracks for playlist add (AJAX)
     */
    public function actionSearch($q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $q = trim((string)$q);
        if ($q === '' || mb_strlen($q) < 2) return [];

        $tracks = Track::find()
            ->where(['like', 'title', $q])
            ->limit(8)
            ->all();

        $out = [];
        foreach ($tracks as $t) {
            $out[] = [
                'id' => (int)$t->id,
                'title' => (string)$t->title,
                'subtitle' => (string)($t->artist_name ?? ''),
            ];
        }

        return $out;
    }

    /**
     * Return my playlists for dropdown (AJAX)
     */
    public function actionMyPlaylists()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = (int)Yii::$app->user->id;

        $pls = Playlist::find()
            ->where(['user_id' => $userId])
            ->andWhere(['<>', 'title', 'Gostos'])
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();

        $out = [];
        foreach ($pls as $p) {
            $out[] = [
                'id' => (int)$p->id,
                'title' => (string)$p->title,
            ];
        }

        return ['success' => true, 'playlists' => $out];
    }

    /**
     * Like a playlist (AJAX)
     */
    public function actionLike($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $playlistId = (int)$id;
        $userId = (int)Yii::$app->user->id;

        // Ensure playlist exists
        if (!Playlist::find()->where(['id' => $playlistId])->exists()) {
            return ['ok' => false, 'message' => 'Playlist not found'];
        }

        // Insert like if not exists
        $exists = (new \yii\db\Query())
            ->from('{{%playlist_like}}')
            ->where(['playlist_id' => $playlistId, 'user_id' => $userId])
            ->exists();

        if (!$exists) {
            Yii::$app->db->createCommand()->insert('{{%playlist_like}}', [
                'playlist_id' => $playlistId,
                'user_id' => $userId,
                'created_at' => new Expression('CURRENT_TIMESTAMP'),
            ])->execute();
        }

        $count = (int)(new \yii\db\Query())
            ->from('{{%playlist_like}}')
            ->where(['playlist_id' => $playlistId])
            ->count();

        return ['ok' => true, 'liked' => true, 'count' => $count];
    }

    /**
     * Unlike a playlist (AJAX)
     */
    public function actionUnlike($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $playlistId = (int)$id;
        $userId = (int)Yii::$app->user->id;

        Yii::$app->db->createCommand()->delete('{{%playlist_like}}', [
            'playlist_id' => $playlistId,
            'user_id' => $userId,
        ])->execute();

        $count = (int)(new \yii\db\Query())
            ->from('{{%playlist_like}}')
            ->where(['playlist_id' => $playlistId])
            ->count();

        return ['ok' => true, 'liked' => false, 'count' => $count];
    }
}
