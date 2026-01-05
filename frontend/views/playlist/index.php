<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\Playlist[] $myPlaylists */
/** @var \common\models\Playlist[] $likedPlaylists */
/** @var \common\models\Playlist[] $suggestedPlaylists */

$this->title = 'Playlists | PHONYX';

$defaultCover = Url::to('@web/img/default-cover.png', true);

$resolveCover = function($playlist) use ($defaultCover) {
    $path = null;

    // Tries cover_asset_id -> Asset path (same style you used)
    if (!empty($playlist->cover_asset_id)) {
        $asset = \common\models\Asset::findOne((int)$playlist->cover_asset_id);
        if ($asset && !empty($asset->path)) $path = (string)$asset->path;
    }

    if (!$path) return $defaultCover;

    if (preg_match('~^https?://~i', $path)) return $path;

    return Url::to('@web/' . ltrim($path, '/'), true);
};

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>

<div class="playlists-page">

  <div class="playlists-head">
    <div>
      <h1 class="playlists-title">Playlists</h1>
      <p class="playlists-sub">Explora e gere as tuas playlists</p>
    </div>

    <a class="btn btn-primary" href="<?= Url::to(['playlist/create']) ?>">Criar playlist</a>
  </div>

  <!-- MY PLAYLISTS -->
  <section class="pl-section">
    <h2 class="pl-h2">As tuas playlists</h2>

    <?php if (empty($myPlaylists)): ?>
      <div class="pl-empty">Ainda não tens playlists.</div>
    <?php else: ?>
      <div class="pl-grid">
        <?php foreach ($myPlaylists as $pl): ?>
          <?php $cover = $resolveCover($pl); ?>
          <a class="pl-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
            <img class="pl-cover" src="<?= Html::encode($cover) ?>" alt="">
            <div class="pl-name"><?= Html::encode($pl->title ?: 'Untitled') ?></div>
            <div class="pl-meta">A tua playlist</div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- LIKED PLAYLISTS -->
  <section class="pl-section">
    <div class="pl-section-row">
      <h2 class="pl-h2">Playlists que deste like</h2>
    </div>

    <?php if (Yii::$app->user->isGuest): ?>
      <div class="pl-empty">Faz login para veres as playlists que gostaste.</div>
    <?php elseif (empty($likedPlaylists)): ?>
      <div class="pl-empty">Ainda não deste like a nenhuma playlist.</div>
    <?php else: ?>
      <div class="pl-grid">
        <?php foreach ($likedPlaylists as $pl): ?>
          <?php $cover = $resolveCover($pl); ?>
          <a class="pl-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
            <img class="pl-cover" src="<?= Html::encode($cover) ?>" alt="">
            <div class="pl-name"><?= Html::encode($pl->title ?: 'Untitled') ?></div>
            <div class="pl-meta">Liked</div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- SUGGESTIONS -->
  <section class="pl-section">
    <div class="pl-section-row">
      <h2 class="pl-h2">Sugestões para ouvir</h2>
    </div>

    <?php if (empty($suggestedPlaylists)): ?>
      <div class="pl-empty">Sem sugestões.</div>
    <?php else: ?>
      <div class="pl-grid">
        <?php foreach ($suggestedPlaylists as $pl): ?>
          <?php
            $cover = $resolveCover($pl);

            // If user is logged in, check liked
            $liked = false;
            if (!Yii::$app->user->isGuest) {
              $liked = (new \yii\db\Query())
                ->from('{{%playlist_like}}')
                ->where(['playlist_id' => (int)$pl->id, 'user_id' => (int)Yii::$app->user->id])
                ->exists();
            }
          ?>
          <div class="pl-card-wrap">
            <a class="pl-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
              <img class="pl-cover" src="<?= Html::encode($cover) ?>" alt="">
              <div class="pl-name"><?= Html::encode($pl->title ?: 'Untitled') ?></div>
              <div class="pl-meta">Sugestão</div>
            </a>

            <?php if (!Yii::$app->user->isGuest): ?>
              <button
                class="pl-like-btn"
                type="button"
                data-id="<?= (int)$pl->id ?>"
                data-liked="<?= $liked ? '1' : '0' ?>"
                data-like-url="<?= Url::to(['playlist/like', 'id' => $pl->id]) ?>"
                data-unlike-url="<?= Url::to(['playlist/unlike', 'id' => $pl->id]) ?>"
                aria-label="Like"
                title="Like"
              ><?= $liked ? '♥' : '♡' ?></button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</div>

<?php
$this->registerJs(<<<JS
(function(){
  function getCsrf() {
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var paramMeta = document.querySelector('meta[name="csrf-param"]');
    return {
      token: tokenMeta ? tokenMeta.getAttribute('content') : '',
      param: paramMeta ? paramMeta.getAttribute('content') : '_csrf'
    };
  }

  document.addEventListener('click', async function(e){
    var btn = e.target.closest('.pl-like-btn');
    if (!btn) return;

    var liked = btn.dataset.liked === '1';
    var url = liked ? btn.dataset.unlikeUrl : btn.dataset.likeUrl;

    var csrf = getCsrf();
    var body = new URLSearchParams();
    body.append(csrf.param, csrf.token);

    btn.disabled = true;

    try {
      var res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body.toString()
      });

      var data = await res.json().catch(function(){ return null; });

      if (!res.ok || !data || !data.ok) {
        console.log('Like failed', res.status, data);
        btn.disabled = false;
        return;
      }

      btn.dataset.liked = data.liked ? '1' : '0';
      btn.textContent = data.liked ? '♥' : '♡';

    } catch (err) {
      console.error(err);
    } finally {
      btn.disabled = false;
    }
  });
})();
JS);
