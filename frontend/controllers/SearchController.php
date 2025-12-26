<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Track;
use common\models\Artist;
use common\models\Album;
use common\models\Playlist;
use common\models\User;

class SearchController extends Controller
{
    public function actionIndex(string $q = '', string $tab = 'all')
    {
        // limpar o texto da pesquisa
        $q = trim(Yii::$app->request->get('q', $q));

        // tabs possíveis
        $validTabs = ['all', 'artists', 'tracks', 'albums', 'playlists', 'profiles'];
        $activeTab = in_array($tab, $validTabs, true) ? $tab : 'all';

        // se não vier nada na pesquisa devolvo arrays vazios
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

        // artistas – uso a tabela 'artists' e a coluna 'stage_name'
        // artistas – uso o nome artístico e a bio
$artists = Artist::find()
->andFilterWhere(['like', 'artist_name', $q])
->orFilterWhere(['like', 'bio', $q])
->all();


        // faixas – pesquiso pelo título
        $tracks = Track::find()
            ->where(['like', 'title', $q])
            ->all();

        // álbuns – pelo título do álbum
        $albums = Album::find()
            ->where(['like', 'title', $q])
            ->all();

        // playlists – pelo título da playlist
        $playlists = Playlist::find()
            ->where(['like', 'title', $q])
            ->all();

        // perfis – procuro username, display_name ou email
        $profiles = User::find()
            ->where(['or',
                ['like', 'username', $q],
                ['like', 'display_name', $q],
                ['like', 'email', $q],
            ])
            ->all();

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
