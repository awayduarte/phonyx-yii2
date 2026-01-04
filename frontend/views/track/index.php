<?php
/** @var yii\web\View $this */
/** @var \common\models\Genre[] $genres */
/** @var array $tracksByGenre */


use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Tracks | PHONYX';
?>

<div class="tracks-page">

    <h1 class="tracks-title">Todas as faixas</h1>
    <p class="tracks-subtitle">
        Explora as músicas da PHONYX por género.
    </p>

    <?php foreach ($genres as $genre): ?>
        <section class="tracks-genre-block">
            <header class="tracks-genre-header">
                <h2 class="tracks-genre-name">
                    <?= Html::encode($genre->name) ?>
                </h2>
            </header>

            <?php $tracks = $tracksByGenre[$genre->id] ?? []; ?>


            <?php if (empty($tracks)): ?>
                <p class="tracks-empty">Nenhuma faixa neste género.</p>
            <?php else: ?>
                <div class="tracks-list">
                    <?php foreach ($tracks as $track): ?>
                        <?php
                        // Audio URL: stored in Asset table and linked by track.audio_asset_id
                        $audioUrl = null;
                        if ($track->audioAsset && !empty($track->audioAsset->path)) {
                            // Asset path is expected to be like: /uploads/tracks/track_xxx.mp3
                            $audioUrl = Yii::getAlias('@web') . $track->audioAsset->path;
                        }

                        // Cover URL: track does not have cover_path in your DB schema.
                        // Use a default cover for now (you can later link a cover asset if you add cover_asset_id).
                        $coverUrl = Yii::getAlias('@web') . '/img/default-cover.png';

                        // Main artist name (via relation)
                        $artistName = $track->artist ? ($track->artist->artist_name ?? $track->artist->stage_name ?? 'Unknown artist') : 'Unknown artist';

                        // Link to track view page
                        $trackUrl = Url::to(['track/view', 'id' => $track->id]);
                        ?>

                        <div class="track-row">
                            <div class="track-main">
                                <a href="<?= $trackUrl ?>" class="track-cover-small">
                                    <img src="<?= Html::encode($coverUrl) ?>" alt="">
                                </a>

                                <div class="track-text">
                                    <a href="<?= $trackUrl ?>" class="track-title-link">
                                        <span class="track-title">
                                            <?= Html::encode($track->title ?? 'Untitled') ?>
                                        </span>
                                    </a>
                                    <span class="track-artist">
                                        <?= Html::encode($artistName) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="track-meta">
                                <?php if (!empty($track->duration)): ?>
                                    <span class="track-duration">
                                        <?= Html::encode($track->duration) ?>
                                    </span>
                                <?php endif; ?>
                                <button class="add-to-playlist-btn" data-track-id="<?= $track->id ?>">
                                    ➕ Playlist
                                </button>

                                <?php if ($audioUrl): ?>

                                    <button type="button" class="track-play-btn" data-id="<?= (int) $track->id ?>"
                                        data-audio="<?= Html::encode($audioUrl) ?>"
                                        data-title="<?= Html::encode($track->title ?? '') ?>"
                                        data-artist="<?= Html::encode($artistName) ?>" data-cover="<?= Html::encode($coverUrl) ?>">
                                        ▶
                                    </button>
                                <?php else: ?>
                                    <span class="track-no-audio">Sem áudio</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

</div>

<?php
// JS: send the selected track to the global player
$this->registerJs(<<<JS
(function() {
  function updateButtons() {
    const player = window.phonyxPlayer;
    if (!player || !player.audio) return;

    const currentId = String(player.currentId || '');
    const isPlaying = !player.audio.paused;

    document.querySelectorAll('.track-play-btn').forEach(btn => {
      const id = String(btn.dataset.id || '');
      const isCurrent = currentId && id === currentId;

      // If this row is the current track and it's playing -> show pause icon
      btn.textContent = (isCurrent && isPlaying) ? '❚❚' : '▶';
    });
  }

  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.track-play-btn');
    if (!btn) return;

    const id     = btn.dataset.id || '';
    const src    = btn.dataset.audio;
    const title  = btn.dataset.title || '';
    const artist = btn.dataset.artist || '';
    const cover  = btn.dataset.cover || '';

    if (!src) return;

    // If same track, just toggle play/pause
    if (typeof window.phonyxTogglePlay === 'function') {
      const toggled = window.phonyxTogglePlay(id);
      if (toggled) {
        updateButtons();
        return;
      }
    }

    // Different track -> set and play
    if (typeof window.phonyxSetTrack === 'function') {
      window.phonyxSetTrack({ id, src, title, artist, cover });
      updateButtons();
    }
  });

  // Keep UI in sync when playback changes
  window.addEventListener('phonyx:trackchange', updateButtons);
  window.addEventListener('phonyx:play', updateButtons);
  window.addEventListener('phonyx:pause', updateButtons);

  document.querySelectorAll('.add-to-playlist-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const trackId = btn.dataset.trackId;
        openPlaylistModal(trackId);
    });
});


  // Initial render
  updateButtons();
})();


JS);
?>