<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use common\models\Track;
use common\models\Artist;
use common\models\Album;
use common\models\Playlist;
use common\models\User;

class SearchController extends Controller
{
    public function actionIndex(string $q = '', string $tab = 'all')
    {
        $q = trim(Yii::$app->request->get('q', $q));


        $validTabs = ['all', 'artists', 'tracks', 'albums', 'playlists', 'profiles'];
        $activeTab = in_array($tab, $validTabs, true) ? $tab : 'all';

        if ($q === '') {
            return $this->render('index', [
                'q' => $q,
                'activeTab' => $activeTab,
                'artists' => [],
                'tracks' => [],
                'albums' => [],
                'playlists' => [],
                'profiles' => [],
            ]);
        }

        // ================= ARTISTS =================
        
        $artists = Artist::find()
            ->where(['like', 'stage_name', $q])
            ->orWhere(['like', 'bio', $q])
            ->with(['user.profileAsset']) // se existir relação
            ->limit(30)
            ->all();

        $artistIds = ArrayHelper::getColumn($artists, 'id');

        // ================= TRACKS =================
        
        $tracksQuery = Track::find()
            ->where(['like', 'title', $q])
            ->orWhere(['like', 'feat', $q])
            ->with([
                'album.coverAsset',
                'artist.user.profileAsset',
            ])
            ->limit(60);

      
        $schema = Track::getTableSchema();
        if ($schema && isset($schema->columns['artist_id']) && !empty($artistIds)) {
            $tracksQuery->orWhere(['artist_id' => $artistIds]);
        } else {
            
            try {
                $tracksQuery->joinWith(['artist a'], false);
                $tracksQuery->orWhere(['like', 'a.stage_name', $q]);
            } catch (\Throwable $e) {
                // ignora
            }
        }

        $tracks = $tracksQuery->all();

        // ================= ALBUMS =================
        $albumsQuery = Album::find()
            ->where(['like', 'title', $q])
            ->with(['coverAsset', 'artist'])
            ->limit(30);

        
        $albumSchema = Album::getTableSchema();
        if ($albumSchema && isset($albumSchema->columns['artist_id']) && !empty($artistIds)) {
            $albumsQuery->orWhere(['artist_id' => $artistIds]);
        }

        $albums = $albumsQuery->all();

        // ================= PLAYLISTS =================
        $playlists = Playlist::find()
            ->where(['like', 'title', $q])
            ->with(['coverAsset', 'owner'])
            ->limit(30)
            ->all();

        // ================= PROFILES =================
        $profiles = User::find()
            ->where(['or',
                ['like', 'username', $q],
                ['like', 'display_name', $q],
                ['like', 'email', $q],
            ])
            ->with(['profileAsset'])
            ->limit(30)
            ->all();

        // ================= TAB FILTER =================
        if ($activeTab !== 'all') {
            if ($activeTab !== 'artists')   $artists = [];
            if ($activeTab !== 'tracks')    $tracks = [];
            if ($activeTab !== 'albums')    $albums = [];
            if ($activeTab !== 'playlists') $playlists = [];
            if ($activeTab !== 'profiles')  $profiles = [];
        }

        return $this->render('index', [
            'q' => $q,
            'activeTab' => $activeTab,
            'artists' => $artists,
            'tracks' => $tracks,
            'albums' => $albums,
            'playlists' => $playlists,
            'profiles' => $profiles,
        ]);
    }
}
