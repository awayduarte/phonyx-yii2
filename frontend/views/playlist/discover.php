<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Asset;


/** @var \common\models\Playlist[] $myPlaylists */
/** @var \common\models\Playlist[] $suggestedPlaylists */
/** @var int|null $userId */
/** @var array $coverMap */


$this->title = 'Playlists';
$this->registerCssFile('@web/css/playlists.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

?>


<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="mb-0">Playlists</h1>

        <?php if ($userId): ?>
            <a class="btn btn-primary" href="<?= Url::to(['playlist/create']) ?>">Criar playlist</a>
        <?php else: ?>
            <a class="btn btn-outline-primary" href="<?= Url::to(['site/login']) ?>">Entrar para criar</a>
        <?php endif; ?>
    </div>

    <div class="mb-5">
        <h3 class="mb-3">As tuas playlists</h3>

        <?php if (!$userId): ?>
            <div class="alert alert-info">Faz login para veres e criares as tuas playlists.</div>
        <?php elseif (empty($myPlaylists)): ?>
            <div class="alert alert-secondary">Ainda não tens playlists.</div>
        <?php else: ?>
            <div class="playlist-grid">
                <?php foreach ($myPlaylists as $pl): ?>
                    <a class="playlist-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
                        <div class="playlist-cover">
                            <?php $coverUrl = $pl->cover_asset_id && isset($coverMap[$pl->cover_asset_id]) ? $coverMap[$pl->cover_asset_id] : null; ?>

                            <div class="playlist-cover">
                                <?php if ($coverUrl): ?>
                                    <img src="<?= Html::encode($coverUrl) ?>?v=<?= (int) $pl->cover_asset_id ?>" alt="Capa">
                                <?php else: ?>
                                    <div class="playlist-cover-placeholder">♪</div>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div class="playlist-info">
                            <div class="playlist-title"><?= Html::encode($pl->title) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div>
        <h3 class="mb-3">Sugestões para ouvir</h3>

        <?php if (empty($suggestedPlaylists)): ?>
            <div class="alert alert-secondary">Ainda não existem playlists sugeridas.</div>
        <?php else: ?>
            <div class="playlist-grid">
                <?php foreach ($suggestedPlaylists as $pl): ?>
                    <a class="playlist-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
                        <div class="playlist-cover">
                            <div class="playlist-cover-placeholder">♪</div>
                        </div>
                        <div class="playlist-info">
                            <div class="playlist-title"><?= Html::encode($pl->title) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>