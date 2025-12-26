<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View   $this
 * @var string         $q
 * @var string         $activeTab
 * @var common\models\Artist[]   $artists
 * @var common\models\Track[]    $tracks
 * @var common\models\Album[]    $albums
 * @var common\models\Playlist[] $playlists
 * @var common\models\User[]     $users
 */

$this->title = 'Resultados da pesquisa';
?>
<div class="search-page">

    <h1 class="search-title">Resultados da pesquisa</h1>

    <div class="search-query-box">
        <span>A pesquisar por</span>
        <strong>"<?= Html::encode($q) ?>"</strong>
    </div>
    <div class="back-home-wrapper">
    <a href="<?= \yii\helpers\Url::to(['site/index']) ?>" class="back-home-btn">
        ← Voltar ao Home
    </a>
</div>


    <!-- barra para voltar a mudar a pesquisa -->
    <form action="<?= Url::to(['search/index']) ?>" method="get" class="search-inline-form">
        <input type="text"
               name="q"
               value="<?= Html::encode($q) ?>"
               class="search-inline-input"
               placeholder="Procurar outra coisa…">
        <button type="submit" class="search-inline-btn">Procurar</button>
    </form>

    <!-- tabs para filtrar o tipo de resultado -->
    <div class="search-tabs">
        <?php
        // pequena função para eu não repetir html
        $tabs = [
            'all'      => 'Tudo',
            'artists'  => 'Artistas',
            'tracks'   => 'Faixas',
            'albums'   => 'Álbuns',
            'playlists'=> 'Playlists',
            'users'    => 'Perfis',
        ];

        foreach ($tabs as $tabKey => $label):
            $isActive = ($activeTab === $tabKey);
            ?>
            <a href="<?= Url::to(['search/index', 'q' => $q, 'tab' => $tabKey]) ?>"
               class="search-tab <?= $isActive ? 'is-active' : '' ?>">
                <?= Html::encode($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ARTISTAS -->
    <?php if ($activeTab === 'all' || $activeTab === 'artists'): ?>
        <section class="search-section">
            <h2 class="search-section-title">Artistas</h2>

            <?php if (empty($artists)): ?>
                <p class="search-empty">Nenhum artista encontrado.</p>
            <?php else: ?>
                <ul class="search-list">
                    <?php foreach ($artists as $artist): ?>
                        <li class="search-item">
                            <div class="search-item-main">
                                <div class="search-item-title">
                                    <?= Html::encode($artist->artist_name) ?>
                                </div>
                                <?php if ($artist->bio): ?>
                                    <div class="search-item-sub">
                                        <?= Html::encode(mb_strimwidth($artist->bio, 0, 120, '…')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="#"
                               class="search-item-pill">
                                Ver artista
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- FAIXAS -->
    <?php if ($activeTab === 'all' || $activeTab === 'tracks'): ?>
        <section class="search-section">
            <h2 class="search-section-title">Faixas</h2>

            <?php if (empty($tracks)): ?>
                <p class="search-empty">Nenhuma faixa encontrada.</p>
            <?php else: ?>
                <ul class="search-list">
                    <?php foreach ($tracks as $track): ?>
                        <li class="search-item">
                            <div class="search-item-main">
                                <div class="search-item-title">
                                    <?= Html::encode($track->title) ?>
                                </div>
                                <?php if ($track->artist): ?>
                                    <div class="search-item-sub">
                                        <?= Html::encode($track->artist->artist_name) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="#"
                               class="search-item-pill">
                                Abrir faixa
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- ÁLBUNS -->
    <?php if ($activeTab === 'all' || $activeTab === 'albums'): ?>
        <section class="search-section">
            <h2 class="search-section-title">Álbuns</h2>

            <?php if (empty($albums)): ?>
                <p class="search-empty">Nenhum álbum encontrado.</p>
            <?php else: ?>
                <ul class="search-list">
                    <?php foreach ($albums as $album): ?>
                        <li class="search-item">
                            <div class="search-item-main">
                                <div class="search-item-title">
                                    <?= Html::encode($album->title) ?>
                                </div>
                                <?php if ($album->artist): ?>
                                    <div class="search-item-sub">
                                        <?= Html::encode($album->artist->artist_name) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="#"
                               class="search-item-pill">
                                Ver álbum
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- PLAYLISTS -->
    <?php if ($activeTab === 'all' || $activeTab === 'playlists'): ?>
        <section class="search-section">
            <h2 class="search-section-title">Playlists</h2>

            <?php if (empty($playlists)): ?>
                <p class="search-empty">Nenhuma playlist encontrada.</p>
            <?php else: ?>
                <ul class="search-list">
                    <?php foreach ($playlists as $playlist): ?>
                        <li class="search-item">
                            <div class="search-item-main">
                                <div class="search-item-title">
                                    <?= Html::encode($playlist->title) ?>
                                </div>
                                <?php if ($playlist->owner): ?>
                                    <div class="search-item-sub">
                                        By <?= Html::encode($playlist->owner->username) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="#"
                               class="search-item-pill">
                                Abrir playlist
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- PERFIS / USERS -->
    <?php if ($activeTab === 'all' || $activeTab === 'users'): ?>
        <section class="search-section">
            <h2 class="search-section-title">Perfis</h2>

            <?php if (empty($users)): ?>
                <p class="search-empty">Nenhum utilizador encontrado.</p>
            <?php else: ?>
                <ul class="search-list">
                    <?php foreach ($users as $user): ?>
                        <li class="search-item">
                            <div class="search-item-main">
                                <div class="search-item-title">
                                    <?= Html::encode($user->username) ?>
                                </div>
                                <div class="search-item-sub">
                                    Utilizador PHONYX
                                </div>
                            </div>
                            <a href="#"
                               class="search-item-pill">
                                Ver perfil
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    <?php endif; ?>

</div>
