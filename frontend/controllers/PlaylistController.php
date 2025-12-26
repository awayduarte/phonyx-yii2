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

class PlaylistController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['toggle-like'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // só user autenticado
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'toggle-like' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Adiciona/remove a faixa atual da playlist "Gostos" do utilizador.
     */
    public function actionToggleLike()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId  = Yii::$app->user->id;
        $trackId = (int)Yii::$app->request->post('track_id');

        if (!$trackId || !Track::find()->where(['id' => $trackId])->exists()) {
            return ['success' => false, 'message' => 'Faixa inválida.'];
        }

        // garantir playlist "Gostos" do utilizador
        $playlist = Playlist::find()
            ->where(['user_id' => $userId, 'name' => 'Gostos'])
            ->one();

        if (!$playlist) {
            $playlist = new Playlist();
            $playlist->user_id = $userId;
            $playlist->name    = 'Gostos';
            $playlist->created_at = time();
            $playlist->updated_at = time();
            if (!$playlist->save(false)) {
                return ['success' => false, 'message' => 'Não foi possível criar a playlist de gostos.'];
            }
        }

        // já está lá? então remove (toggle)
        $link = PlaylistTrack::find()
            ->where(['playlist_id' => $playlist->id, 'track_id' => $trackId])
            ->one();

        if ($link) {
            $link->delete();
            return [
                'success' => true,
                'state'   => 'removed',
            ];
        }

        // se ainda não está, adiciona
        $link = new PlaylistTrack();
        $link->playlist_id = $playlist->id;
        $link->track_id    = $trackId;
        $link->created_at  = time();

        if ($link->save(false)) {
            return [
                'success' => true,
                'state'   => 'added',
            ];
        }

        return ['success' => false, 'message' => 'Erro ao gravar nos gostos.'];
    }
}
