<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\Playlist[] $myPlaylists */
/** @var \common\models\Playlist[] $suggestedPlaylists */
/** @var \common\models\Playlist[] $likedPlaylists */
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

    if (preg_match('~^https?://~i', $path)) return $path;
    if ($path !== '' && $path[0] !== '/') $path = '/' . $path;

    return Url::to('@web' . $path, true);
};

// Helper: playlist is liked by me?
$likedIds = [];
if (!empty($likedPlaylists)) {
    foreach ($likedPlaylists as $lp) $likedIds[(int)$lp->id] = true;
}

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();
?>

<div class="container py-4 playlist-discover">

  <div class="pd-header">
    <div>
      <h1 class="pd-title">Playlists</h1>
      <p class="pd-sub">Explora e gere as tuas playlists</p>
    </div>

    <?php if (!empty($userId)): ?>
      <a class="pd-btn" href="<?= Url::to(['playlist/create']) ?>">Criar playlist</a>
    <?php else: ?>
      <a class="pd-btn" style="background:transparent;border:1px solid rgba(255,255,255,.18)" href="<?= Url::to(['site/login']) ?>">
        Entrar para criar
      </a>
    <?php endif; ?>
  </div>

  <!-- LIKED PLAYLISTS -->
  <?php if (!empty($userId)): ?>
    <section class="pd-section">
      <div class="pd-section-head">
        <h2 class="pd-h2">Playlists que curtiste</h2>
      </div>

      <?php if (empty($likedPlaylists)): ?>
        <div class="pd-empty">Ainda não curtiste playlists.</div>
      <?php else: ?>
        <div class="pd-grid">
          <?php foreach ($likedPlaylists as $pl): ?>
            <?php $coverUrl = $resolveCover($pl); ?>
            <a class="pd-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
              <div class="pd-cover">
                <img src="<?= Html::encode($coverUrl) ?>?v=<?= (int)$pl->cover_asset_id ?: time() ?>" alt="Capa">
              </div>
              <div class="pd-meta">
                <p class="pd-name"><?= Html::encode($pl->title ?: 'Untitled') ?></p>
                <div class="pd-type">Liked</div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <!-- MY PLAYLISTS -->
  <section class="pd-section">
    <div class="pd-section-head">
      <h2 class="pd-h2">As tuas playlists</h2>
    </div>

    <?php if (empty($userId)): ?>
      <div class="pd-empty">Faz login para veres e criares as tuas playlists.</div>
    <?php elseif (empty($myPlaylists)): ?>
      <div class="pd-empty">Ainda não tens playlists.</div>
    <?php else: ?>
      <div class="pd-grid">
        <?php foreach ($myPlaylists as $pl): ?>
          <?php $coverUrl = $resolveCover($pl); ?>
          <a class="pd-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
            <div class="pd-cover">
              <img src="<?= Html::encode($coverUrl) ?>?v=<?= (int)$pl->cover_asset_id ?: time() ?>" alt="Capa">
            </div>
            <div class="pd-meta">
              <p class="pd-name"><?= Html::encode($pl->title ?: 'Untitled') ?></p>
              <div class="pd-type">Playlist</div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- SUGGESTED -->
  <section class="pd-section">
    <div class="pd-section-head">
      <h2 class="pd-h2">Sugestões para ouvir</h2>
    </div>

    <?php if (empty($suggestedPlaylists)): ?>
      <div class="pd-empty">Ainda não existem playlists sugeridas.</div>
    <?php else: ?>
      <div class="pd-grid">
        <?php foreach ($suggestedPlaylists as $pl): ?>
          <?php
            $coverUrl = $resolveCover($pl);
            $isLiked = !empty($likedIds[(int)$pl->id]);
          ?>
          <div style="position:relative">
            <a class="pd-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
              <div class="pd-cover">
                <img src="<?= Html::encode($coverUrl) ?>?v=<?= (int)$pl->cover_asset_id ?: time() ?>" alt="Capa">
              </div>
              <div class="pd-meta">
                <p class="pd-name"><?= Html::encode($pl->title ?: 'Untitled') ?></p>
                <div class="pd-type">Sugestão</div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</div>

<?php
$this->registerJs(<<<JS
(function(){
  const csrfParam = {$this->renderDynamic('return json_encode(Yii::$app->request->csrfParam);')};
  const csrfToken = {$this->renderDynamic('return json_encode(Yii::$app->request->csrfToken);')};

  async function post(url){
    const body = new URLSearchParams();
    body.append(csrfParam, csrfToken);

    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: body.toString()
    });

    const txt = await res.text();
    try { return { ok: res.ok, data: JSON.parse(txt) }; }
    catch(e){ console.log('RAW:', txt); return { ok: false, data: null }; }
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.pl-like-btn');
    if (!btn) return;

    const liked = btn.dataset.liked === '1';
    const url = liked ? btn.dataset.unlikeUrl : btn.dataset.likeUrl;

    const r = await post(url);
    if (!r.ok || !r.data || !r.data.ok) return;

    btn.dataset.liked = r.data.liked ? '1' : '0';
    btn.classList.toggle('is-liked', !!r.data.liked);
    btn.textContent = r.data.liked ? '♥' : '♡';

  });
})();
JS);
?>
