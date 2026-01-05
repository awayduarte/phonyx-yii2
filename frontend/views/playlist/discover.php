<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\Playlist[] $myPlaylists */
/** @var \common\models\Playlist[] $suggestedPlaylists */
/** @var int|null $userId */

$this->title = 'Playlists';
$this->registerCssFile('@web/css/playlists.css?v=' . time(), [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$defaultCover = Url::to('@web/img/default-cover.png', true);

$resolveCover = function($playlist) use ($defaultCover) {
    if (empty($playlist->cover_asset_id)) return $defaultCover;

    $asset = \common\models\Asset::findOne((int)$playlist->cover_asset_id);
    if (!$asset || empty($asset->path)) return $defaultCover;

    $path = (string)$asset->path;

    // se já for URL absoluta
    if (preg_match('~^https?://~i', $path)) return $path;

    // normaliza para começar com /
    if ($path !== '' && $path[0] !== '/') $path = '/' . $path;

    // garante URL absoluta no frontend
    return Url::to('@web' . $path, true);
};
?>

<div class="container py-4 playlists-discover">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="mb-0">Playlists</h1>
      <div class="text-muted">Explora e gere as tuas playlists</div>
    </div>

    <?php if (!empty($userId)): ?>
      <a class="btn btn-primary" href="<?= Url::to(['playlist/create']) ?>">Criar playlist</a>
    <?php else: ?>
      <a class="btn btn-outline-primary" href="<?= Url::to(['site/login']) ?>">Entrar para criar</a>
    <?php endif; ?>
  </div>

  <section class="mb-5">
    <h3 class="mb-3">As tuas playlists</h3>

    <?php if (empty($userId)): ?>
      <div class="alert alert-info">Faz login para veres e criares as tuas playlists.</div>
    <?php elseif (empty($myPlaylists)): ?>
      <div class="alert alert-secondary">Ainda não tens playlists.</div>
    <?php else: ?>
      <div class="playlist-grid">
        <?php foreach ($myPlaylists as $pl): ?>
          <?php $coverUrl = $resolveCover($pl); ?>
          <a class="playlist-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
            <div class="playlist-cover">
              <img src="<?= Html::encode($coverUrl) ?>?v=<?= (int)$pl->cover_asset_id ?: time() ?>" alt="Capa">
            </div>
            <div class="playlist-info">
              <div class="playlist-title"><?= Html::encode($pl->title) ?></div>
              <div class="playlist-sub">Playlist</div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section>
    <h3 class="mb-3">Sugestões para ouvir</h3>

    <?php if (empty($suggestedPlaylists)): ?>
      <div class="alert alert-secondary">Ainda não existem playlists sugeridas.</div>
    <?php else: ?>
      <div class="playlist-grid">
        <?php foreach ($suggestedPlaylists as $pl): ?>
          <?php $coverUrl = $resolveCover($pl); ?>
          <a class="playlist-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
            <div class="playlist-cover">
              <img src="<?= Html::encode($coverUrl) ?>?v=<?= (int)$pl->cover_asset_id ?: time() ?>" alt="Capa">
            </div>
            <div class="playlist-info">
              <div class="playlist-title"><?= Html::encode($pl->title) ?></div>
              <div class="playlist-sub">Sugestão</div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</div>
