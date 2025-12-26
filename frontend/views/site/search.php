<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $q */
/** @var string $type */
/** @var array $results */

$tabs = [
    'all' => 'All',
    'playlists' => 'Playlists',
    'songs' => 'Songs',
    'artists' => 'Artists',
    'profiles' => 'Profiles',
    'albums' => 'Albums',
];
?>

<div class="search-page">
    <div class="search-header">
        <h1 class="search-title">Pesquisar</h1>

        <form class="search-bar" method="get" action="<?= Url::to(['site/search']) ?>">
            <input
                class="search-input"
                type="text"
                name="q"
                value="<?= Html::encode($q) ?>"
                placeholder="O que queres ouvir?"
                autocomplete="off"
            />
            <input type="hidden" name="type" value="<?= Html::encode($type ?: 'all') ?>">
        </form>

        <div class="search-tabs">
            <?php foreach ($tabs as $key => $label): ?>
                <a class="search-tab <?= $type === $key ? 'active' : '' ?>"
                   href="<?= Url::to(['site/search', 'q' => $q, 'type' => $key]) ?>">
                    <?= Html::encode($label) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SONGS -->
    <div class="search-section">
        <div class="search-section-title">Songs</div>

        <div class="song-list">
            <?php $tracks = $results['tracks'] ?? []; ?>

            <?php if (empty($tracks)): ?>
                <div class="search-empty" style="padding: 12px 14px;">Sem músicas.</div>
            <?php else: ?>
                <?php foreach ($tracks as $t): ?>
                    <?php
                        // Audio URL comes from Asset table: track.audioAsset.path
                        $audioUrl = ($t->audioAsset && !empty($t->audioAsset->path))
                            ? Yii::getAlias('@web') . $t->audioAsset->path
                            : '';

                        // Artist name comes from Artist table: stage_name
                        $artistName = $t->artist ? ($t->artist->stage_name ?? 'Unknown artist') : 'Unknown artist';

                        // Default cover (track does not have cover_path in your schema)
                        $coverUrl = Yii::getAlias('@web') . '/img/default-cover.png';
                    ?>

                    <a class="song-row"
                       href="<?= Url::to(['track/view', 'id' => $t->id]) ?>"
                       data-track-id="<?= (int)$t->id ?>"
                       data-audio-src="<?= Html::encode($audioUrl) ?>"
                       data-title="<?= Html::encode($t->title ?? '') ?>"
                       data-artist="<?= Html::encode($artistName) ?>"
                       data-cover="<?= Html::encode($coverUrl) ?>">

                        <div class="song-cover"></div>

                        <div class="song-meta">
                            <div class="song-title"><?= Html::encode($t->title ?? 'Sem título') ?></div>
                            <div class="song-artist"><?= Html::encode($artistName) ?></div>
                        </div>

                        <div class="song-duration"><?= Html::encode($t->duration ?? '') ?></div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- FEATURING (sample cards) -->
    <div class="search-section">
        <div class="search-section-title"></div>

        <div class="featuring-grid">
            <?php $playlists = $results['playlists'] ?? []; ?>
            <?php foreach (array_slice($playlists, 0, 2) as $pl): ?>
                <a class="feat-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
                    <div class="feat-cover"></div>
                    <div>
                        <div class="feat-type">PLAYLIST</div>
                        <div class="feat-title"><?= Html::encode($pl->title ?? 'Untitled') ?></div>
                        <div class="feat-sub">By PHONYX</div>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php $artists = $results['artists'] ?? []; ?>
            <?php foreach (array_slice($artists, 0, 2) as $a): ?>
                <a class="feat-card" href="<?= Url::to(['artist/view', 'id' => $a->id]) ?>">
                    <div class="feat-cover"></div>
                    <div>
                        <div class="feat-type">ARTIST</div>
                        <div class="feat-title"><?= Html::encode($a->stage_name ?? 'Unknown artist') ?></div>
                        <div class="feat-sub">By PHONYX</div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// Use the global player hook (same approach as tracks/index page)
$js = <<<JS
document.addEventListener('click', function(e) {
  const row = e.target.closest('.song-row');
  if (!row) return;

  const src = row.getAttribute('data-audio-src');
  if (!src) return; // no audio -> allow navigation to track/view

  // If you have a global player function, use it
  if (typeof window.phonyxSetTrack === 'function') {
    e.preventDefault();

    window.phonyxSetTrack({
      id: row.getAttribute('data-track-id') || '',
      src: src,
      title: row.getAttribute('data-title') || '',
      artist: row.getAttribute('data-artist') || '',
      cover: row.getAttribute('data-cover') || ''
    });
  }
});
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>
