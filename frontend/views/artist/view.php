<?php
/** @var yii\web\View $this */
/** @var common\models\Artist $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = ($model->stage_name ?: 'Artist') . ' | PHONYX';

/**
 * Resolve um Asset->path para URL web válida, com fallback.
 * - aceita path absoluto (http/https)
 * - aceita path relativo (uploads/...)
 */
$resolveAssetUrl = function ($asset, $fallbackWebPath) {
    $fallback = Yii::getAlias('@web') . '/' . ltrim($fallbackWebPath, '/');

    if (!$asset || empty($asset->path)) {
        return $fallback;
    }

    $p = (string)$asset->path;

    // URL absoluta
    if (preg_match('~^https?://~i', $p)) {
        return $p;
    }

    // path relativo
    return Yii::getAlias('@web') . '/' . ltrim($p, '/');
};

// Avatar fallback (corrigido via Asset)
$avatarUrl = $resolveAssetUrl($model->avatarAsset ?? null, 'img/default-avatar.png');

// Linked user (optional info)
$user = $model->user ?? null;

// Check if current logged user follows this artist
$isFollowing = false;
if (!Yii::$app->user->isGuest) {
    $isFollowing = (new \yii\db\Query())
        ->from('follow')
        ->where([
            'follower_id' => (int)Yii::$app->user->id,
            'artist_id' => (int)$model->id
        ])
        ->exists();
}
?>

<div class="artist-page">

    <section class="artist-hero">
        <div class="artist-avatar-big">
            <img src="<?= Html::encode($avatarUrl) ?>"
                 alt="Artist photo <?= Html::encode($model->stage_name) ?>">
        </div>

        <div class="artist-main">
            <span class="artist-label">ARTIST</span>

            <h1 class="artist-name">
                <?= Html::encode($model->stage_name) ?>
            </h1>

            <div class="artist-meta-row">
                <?php if ($user): ?>
                    <span class="artist-meta-item">
                        Linked to <?= Html::encode($user->username ?? $user->email) ?>
                    </span>
                    <span class="artist-dot">•</span>
                <?php endif; ?>

                <span class="artist-meta-item">
                    On PHONYX since <?= Yii::$app->formatter->asDate($model->created_at) ?>
                </span>
            </div>

            <?php if (!empty($model->bio)): ?>
                <p class="artist-bio">
                    <?= nl2br(Html::encode($model->bio)) ?>
                </p>
            <?php else: ?>
                <p class="artist-bio artist-bio-empty">
                    This artist hasn't written a bio yet.
                </p>
            <?php endif; ?>

            <div class="artist-actions-row">
                <!-- Plays the first available track from this artist -->
                <button type="button" class="btn btn-accent artist-btn-main" id="artist-play-top">
                    ▶ Play top tracks
                </button>

                <!-- Follow / unfollow (AJAX). Requires routes: artist/follow and artist/unfollow -->
                <button
                    type="button"
                    class="btn btn-ghost artist-btn-secondary <?= $isFollowing ? 'is-following' : '' ?>"
                    id="artist-follow-btn"
                    data-artist-id="<?= (int)$model->id ?>"
                    data-following="<?= $isFollowing ? '1' : '0' ?>"
                    data-follow-url="<?= Url::to(['artist/follow', 'id' => $model->id]) ?>"
                    data-unfollow-url="<?= Url::to(['artist/unfollow', 'id' => $model->id]) ?>"
                >
                    <span class="artist-heart"><?= $isFollowing ? '♥' : '♡' ?></span>
                    <span class="artist-follow-label"><?= $isFollowing ? 'Following' : 'Follow' ?></span>
                </button>
            </div>
        </div>
    </section>

    <section class="artist-section">
        <header class="artist-section-header">
            <h2>Top tracks</h2>
            <p class="artist-section-subtitle">
                Latest or strongest tracks from this artist.
            </p>
        </header>

        <?php $tracks = $model->tracks ?? []; ?>

        <?php if (empty($tracks)): ?>
            <p class="artist-empty">
                There are no tracks linked to this artist yet.
            </p>
        <?php else: ?>
            <div class="artist-tracks-list">
                <?php foreach ($tracks as $track): ?>
                    <?php
                        // Cover (mantém default)
                        $coverUrl = Yii::getAlias('@web') . '/img/default-cover.png';

                        // Audio: track.audio_asset_id -> asset.path
                        $audioUrl = null;
                        if ($track->audioAsset && !empty($track->audioAsset->path)) {
                            $p = (string)$track->audioAsset->path;
                            $audioUrl = preg_match('~^https?://~i', $p)
                                ? $p
                                : (Yii::getAlias('@web') . '/' . ltrim($p, '/'));
                        }

                        $trackUrl = Url::to(['track/view', 'id' => $track->id]);

                        $durationLabel = method_exists($track, 'getDurationLabel')
                            ? $track->durationLabel
                            : ($track->duration ?? '');
                    ?>

                    <div class="artist-track-row">
                        <div class="artist-track-main">
                            <a href="<?= $trackUrl ?>" class="artist-track-cover">
                                <img src="<?= Html::encode($coverUrl) ?>" alt="">
                            </a>

                            <div class="artist-track-text">
                                <a href="<?= $trackUrl ?>" class="artist-track-title-link">
                                    <span class="artist-track-title">
                                        <?= Html::encode($track->title ?? 'Untitled') ?>
                                    </span>
                                </a>
                                <span class="artist-track-meta">
                                    <?= Html::encode($durationLabel ?: '–:–') ?>
                                </span>
                            </div>
                        </div>

                        <div class="artist-track-actions">
                            <?php if ($audioUrl): ?>
                                <button
                                    type="button"
                                    class="artist-track-play-btn"
                                    data-id="<?= (int)$track->id ?>"
                                    data-audio="<?= Html::encode($audioUrl) ?>"
                                    data-title="<?= Html::encode($track->title ?? '') ?>"
                                    data-artist="<?= Html::encode($model->stage_name) ?>"
                                    data-cover="<?= Html::encode($coverUrl) ?>"
                                >
                                    ▶
                                </button>
                            <?php else: ?>
                                <span class="artist-track-no-audio">
                                    No audio
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="artist-section">
        <header class="artist-section-header">
            <h2>Albums</h2>
            <p class="artist-section-subtitle">
                When this artist starts creating albums, they will appear here.
            </p>
        </header>

        <?php $albums = $model->albums ?? []; ?>

        <?php if (empty($albums)): ?>
            <p class="artist-empty">
                No albums for this artist yet.
            </p>
        <?php else: ?>
            <div class="artist-albums-grid">
                <?php foreach ($albums as $album): ?>
                    <?php
                        // ✅ capa real via coverAsset->path (cover_asset_id)
                        $albumCover = $resolveAssetUrl($album->coverAsset ?? null, 'img/default-cover.png');
                        $albumUrl = Url::to(['album/view', 'id' => $album->id]);
                    ?>

                    <a href="<?= $albumUrl ?>" class="artist-album-card">
                        <div class="artist-album-cover">
                            <img src="<?= Html::encode($albumCover) ?>"
                                 alt="<?= Html::encode($album->title ?: 'Album') ?>">
                        </div>

                        <div class="artist-album-text">
                            <span class="artist-album-title">
                                <?= Html::encode($album->title ?: 'Sem título') ?>
                            </span>
                            <span class="artist-album-meta">
                                <?= Yii::$app->formatter->asDate($album->created_at) ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php
$this->registerJs(<<<JS
(function () {
  // Play a specific track row
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.artist-track-play-btn');
    if (!btn || typeof window.phonyxSetTrack !== 'function') return;

    var src = btn.dataset.audio;
    if (!src) return;

    window.phonyxSetTrack({
      id: btn.dataset.id || '',
      src: src,
      title: btn.dataset.title || '',
      artist: btn.dataset.artist || '',
      cover: btn.dataset.cover || ''
    });
  });

  // Play top tracks (first track that has data-audio)
  var playTop = document.getElementById('artist-play-top');
  if (playTop) {
    playTop.addEventListener('click', function() {
      var first = document.querySelector('.artist-track-play-btn[data-audio]');
      if (!first || typeof window.phonyxSetTrack !== 'function') return;

      window.phonyxSetTrack({
        id: first.dataset.id || '',
        src: first.dataset.audio || '',
        title: first.dataset.title || '',
        artist: first.dataset.artist || '',
        cover: first.dataset.cover || ''
      });
    });
  }

  // Follow / unfollow (AJAX)
  var followBtn = document.getElementById('artist-follow-btn');
  if (followBtn) {
    followBtn.addEventListener('click', async function() {
      var isFollowing = this.dataset.following === '1';
      var url = isFollowing ? this.dataset.unfollowUrl : this.dataset.followUrl;

      try {
        var res = await fetch(url, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        var data = await res.json();
        if (!data || !data.ok) return;

        this.dataset.following = data.following ? '1' : '0';
        this.classList.toggle('is-following', !!data.following);

        var heart = this.querySelector('.artist-heart');
        var label = this.querySelector('.artist-follow-label');
        if (heart) heart.textContent = data.following ? '♥' : '♡';
        if (label) label.textContent = data.following ? 'Following' : 'Follow';
      } catch (err) {
        console.error(err);
      }
    });
  }
})();
JS);
?>
