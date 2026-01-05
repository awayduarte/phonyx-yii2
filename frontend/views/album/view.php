<?php
/** @var yii\web\View $this */
/** @var common\models\Album $album */

use yii\helpers\Html;
use yii\helpers\Url;

$model = $album;

$this->title = (($model->title ?? 'Album')) . ' | PHONYX';



$resolveAssetUrl = function ($asset, $fallbackWebPath) {
    $fallback = Yii::getAlias('@web') . '/' . ltrim($fallbackWebPath, '/');

    if (!$asset || empty($asset->path)) {
        return $fallback;
    }

    $p = (string)$asset->path;

    if (preg_match('~^https?://~i', $p)) {
        return $p;
    }

    return Yii::getAlias('@web') . '/' . ltrim($p, '/');
};

$albumCoverUrl = $resolveAssetUrl($model->coverAsset ?? null, 'img/default-cover.png');

$jsTracks = [];
foreach ($tracks as $t) {
    $audioUrl = null;
    if ($t->audioAsset && !empty($t->audioAsset->path)) {
        $p = (string)$t->audioAsset->path;
        $audioUrl = preg_match('~^https?://~i', $p) ? $p : (Yii::getAlias('@web') . '/' . ltrim($p, '/'));
    }

    $jsTracks[] = [
        'id' => (string)$t->id,
        'src' => $audioUrl ?: '',
        'title' => (string)($t->title ?? ''),
        'artist' => (string)($artist->stage_name ?? ''),
        'cover' => (string)$albumCoverUrl,
        'hasAudio' => (bool)$audioUrl,
    ];
}

$trackCount = is_array($tracks) ? count($tracks) : 0;
?>

<div class="album-page">

    <!-- HERO -->
    <section class="album-hero artist-dash-card">
        <div class="album-hero-left">
            <div class="album-cover-big">
                <img src="<?= Html::encode($albumCoverUrl) ?>" alt="Album cover">
            </div>
        </div>

        <div class="album-hero-right">
            <div class="album-chip">ALBUM</div>

            <h1 class="album-title"><?= Html::encode($model->title ?: 'Sem título') ?></h1>

            <div class="album-meta">
                <span><?= Html::encode($artist->stage_name ?? '—') ?></span>
                <span class="dot">•</span>
                <span><?= (int)$trackCount ?> faixas</span>
            </div>

            <div class="album-actions">
                <button
                    type="button"
                    class="artist-dash-pill"
                    id="album-play-btn"
                    <?= empty(array_filter($jsTracks, fn($x)=>$x['hasAudio'])) ? 'disabled' : '' ?>
                    style="display:inline-flex;align-items:center;gap:10px;"
                >
                    ▶ Play album
                </button>

                <a href="<?= Url::to(['artist/dashboard']) ?>" class="artist-dash-back">
                    ← Voltar ao painel
                </a>

                <a href="<?= Url::to(['album/update', 'id' => $model->id]) ?>" class="artist-dash-pill ghost">
                    Edit album
                </a>
            </div>

            <?php if (empty(array_filter($jsTracks, fn($x)=>$x['hasAudio']))): ?>
                <div class="artist-dash-note" style="margin-top:10px;">
                    (Ainda não há áudio nas faixas deste álbum.)
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- TRACKS -->
    <section class="artist-dash-card" style="margin-top:18px;">
        <h2 style="margin:0 0 12px;">Faixas</h2>

        <?php if (empty($tracks)): ?>
            <p class="artist-dash-note">Este álbum ainda não tem faixas.</p>
        <?php else: ?>
            <div class="artist-tracks-list">
                <?php foreach ($tracks as $t): ?>
                    <?php
                        $audioUrl = null;
                        if ($t->audioAsset && !empty($t->audioAsset->path)) {
                            $p = (string)$t->audioAsset->path;
                            $audioUrl = preg_match('~^https?://~i', $p) ? $p : (Yii::getAlias('@web') . '/' . ltrim($p, '/'));
                        }
                    ?>

                    <div class="artist-track-row" style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-top:1px solid rgba(255,255,255,.06);">
                        <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                            <div style="width:44px;height:44px;border-radius:10px;overflow:hidden;flex:0 0 auto;">
                                <img src="<?= Html::encode($albumCoverUrl) ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;">
                            </div>

                            <div style="min-width:0;">
                                <div style="color:#fff;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?= Html::encode($t->title ?: 'Sem título') ?>
                                </div>

                                <div style="opacity:.75;font-size:13px;">
                                    <?= Yii::$app->formatter->asDate($t->created_at) ?>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if ($audioUrl): ?>
                                <button
                                    type="button"
                                    class="dash-play-btn"
                                    data-id="<?= (int)$t->id ?>"
                                    data-audio="<?= Html::encode($audioUrl) ?>"
                                    data-title="<?= Html::encode($t->title ?? '') ?>"
                                    data-artist="<?= Html::encode($artist->stage_name ?? '') ?>"
                                    data-cover="<?= Html::encode($albumCoverUrl) ?>"
                                    style="width:42px;height:42px;border-radius:999px;border:1px solid rgba(255,255,255,.18);background:transparent;color:#fff;"
                                    aria-label="Play"
                                >▶</button>
                            <?php else: ?>
                                <span style="opacity:.6;font-size:13px;">Sem áudio</span>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php

$jsTracksJson = json_encode($jsTracks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$this->registerJs(<<<JS
(function(){
  const albumTracks = $jsTracksJson;

  function firstPlayableIndex() {
    return albumTracks.findIndex(t => t && t.src);
  }

  const playAlbumBtn = document.getElementById('album-play-btn');
  if (playAlbumBtn) {
    playAlbumBtn.addEventListener('click', function(){
      if (typeof window.phonyxSetTrack !== 'function') return;

      const idx = firstPlayableIndex();
      if (idx < 0) {
        alert('Este álbum ainda não tem faixas com áudio.');
        return;
      }

      const first = albumTracks[idx];

      window.phonyxSetTrack({
        id: first.id,
        src: first.src,
        title: first.title,
        artist: first.artist,
        cover: first.cover,
        queue: albumTracks.filter(t => t && t.src),
        queueIndex: albumTracks.filter(t => t && t.src).findIndex(t => t.id === first.id),
      });
    });
  }


  document.addEventListener('click', function(e){
    const btn = e.target.closest('.dash-play-btn');
    if (!btn) return;
    if (typeof window.phonyxSetTrack !== 'function') return;

    const src = btn.dataset.audio;
    if (!src) return;

    window.phonyxSetTrack({
      id: btn.dataset.id || '',
      src: src,
      title: btn.dataset.title || '',
      artist: btn.dataset.artist || '',
      cover: btn.dataset.cover || ''
    });
  });
})();
JS);
?>
