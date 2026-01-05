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

$defaultCover  = Url::to('@web/img/default-cover.png');       
$defaultAvatar = Url::to('@web/images/default-avatar.png');   

$toWebUrl = function (?string $path, string $fallback) {
    if (!$path) return $fallback;
    if (preg_match('~^https?://~i', $path)) return $path;
    $path = '/' . ltrim($path, '/');
    return Yii::getAlias('@web') . $path;
};

$safe = function($obj, string $chain) {
    try {
        $parts = explode('.', $chain);
        $cur = $obj;
        foreach ($parts as $p) {
            if ($cur === null) return null;
            $cur = $cur->$p;
        }
        return $cur;
    } catch (\Throwable $e) {
        return null;
    }
};
?>

<div class="search-page">
    <div class="search-header">
        <h1 class="search-title">Pesquisar</h1>

        <form class="search-bar" method="get" action="<?= Url::to(['site/search']) ?>">
            <input class="search-input" type="text" name="q" value="<?= Html::encode($q) ?>"
                   placeholder="O que queres ouvir?" autocomplete="off" />
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
    <?php if ($type === 'all' || $type === 'songs'): ?>
    <div class="search-section">
        <div class="search-section-title">Songs</div>

        <div class="song-list">
            <?php $tracks = $results['tracks'] ?? []; ?>

            <?php if (empty($tracks)): ?>
                <div class="search-empty" style="padding: 12px 14px;">Sem músicas.</div>
            <?php else: ?>
                <?php foreach ($tracks as $t): ?>
                    <?php
                    $audioPath = $safe($t, 'audioAsset.path');
                    $audioUrl  = $toWebUrl($audioPath, '');

                  
                    $artistName = $safe($t, 'artist.stage_name') ?: ($safe($t, 'artist.artist_name') ?: 'Unknown artist');

                    
                    $coverPath =
                        ($safe($t, 'album.coverAsset.path'))
                        ?: ($safe($t, 'coverAsset.path'))
                        ?: null;

                    $coverUrl = $toWebUrl($coverPath, $defaultCover);
                    ?>

                    <div class="song-row"
                         style="display:flex;align-items:center;gap:14px;"
                         data-track-id="<?= (int)$t->id ?>"
                         data-audio-src="<?= Html::encode($audioUrl) ?>"
                         data-title="<?= Html::encode($t->title ?? '') ?>"
                         data-artist="<?= Html::encode($artistName) ?>"
                         data-cover="<?= Html::encode($coverUrl) ?>">

                        <img src="<?= Html::encode($coverUrl) ?>"
                             alt=""
                             width="56" height="56"
                             style="border-radius:12px;object-fit:cover;"
                             onerror="this.src='<?= Html::encode($defaultCover) ?>'">

                        <div class="song-meta" style="flex:1;min-width:0;">
                            <a href="<?= Url::to(['track/view', 'id' => $t->id]) ?>"
                               class="song-title"
                               style="display:block;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= Html::encode($t->title ?? 'Sem título') ?>
                            </a>
                            <div class="song-artist" style="opacity:.7;">
                                <?= Html::encode($artistName) ?>
                            </div>
                        </div>

                        <!-- PLAY VISÍVEL -->
                        <button type="button"
                                class="js-play"
                                style="padding:10px 14px;border-radius:999px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.06);color:#fff;cursor:pointer;"
                                <?= $audioUrl ? '' : 'disabled' ?>
                                title="<?= $audioUrl ? 'Play' : 'Sem ficheiro de áudio' ?>"
                        >
                            ▶
                        </button>

                        <div class="song-duration" style="opacity:.7;">
                            <?= Html::encode($t->duration ?? '') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>


    <!-- FEATURE GRID: Playlists + Artists  -->
    <?php if ($type === 'all'): ?>
    <div class="search-section">
        <div class="search-section-title"></div>

        <div class="featuring-grid">
            <?php $playlists = $results['playlists'] ?? []; ?>
            <?php foreach (array_slice($playlists, 0, 2) as $pl): ?>
                <?php
                $plCoverPath = $safe($pl, 'coverAsset.path');
                $plCoverUrl  = $toWebUrl($plCoverPath, $defaultCover);
                $ownerName   = $safe($pl, 'owner.username') ?: 'PHONYX';
                ?>
                <a class="feat-card" href="<?= Url::to(['playlist/view', 'id' => $pl->id]) ?>">
                    <img src="<?= Html::encode($plCoverUrl) ?>" alt=""
                         width="64" height="64"
                         style="border-radius:14px;object-fit:cover;"
                         onerror="this.src='<?= Html::encode($defaultCover) ?>'">
                    <div>
                        <div class="feat-type">PLAYLIST</div>
                        <div class="feat-title"><?= Html::encode($pl->title ?? 'Untitled') ?></div>
                        <div class="feat-sub">By <?= Html::encode($ownerName) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php $artists = $results['artists'] ?? []; ?>
            <?php foreach (array_slice($artists, 0, 2) as $a): ?>
                <?php
                
                $avatarPath =
                    ($safe($a, 'user.profileAsset.path'))
                    ?: ($safe($a, 'profileAsset.path'))
                    ?: null;

                $avatarUrl = $toWebUrl($avatarPath, $defaultAvatar);

                $artistName = $a->stage_name ?? $a->artist_name ?? 'Unknown artist';
                ?>
                <a class="feat-card" href="<?= Url::to(['artist/view', 'id' => $a->id]) ?>">
                    <img src="<?= Html::encode($avatarUrl) ?>" alt=""
                         width="64" height="64"
                         style="border-radius:14px;object-fit:cover;"
                         onerror="this.src='<?= Html::encode($defaultAvatar) ?>'">
                    <div>
                        <div class="feat-type">ARTIST</div>
                        <div class="feat-title"><?= Html::encode($artistName) ?></div>
                        <div class="feat-sub">By PHONYX</div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($type === 'all' || $type === 'albums'): ?>
<div class="search-section">
    <div class="search-section-title">Albums</div>

    <?php $albums = $results['albums'] ?? []; ?>

    <?php if (empty($albums)): ?>
        <div class="search-empty" style="padding: 12px 14px;">Sem álbuns.</div>
    <?php else: ?>
        <div class="featuring-grid">
            <?php foreach ($albums as $al): ?>
                <?php
                $defaultCover = Yii::getAlias('@web') . '/img/default-cover.png';

                $coverUrl = $defaultCover;
                if ($al->coverAsset && !empty($al->coverAsset->path)) {
                    $coverUrl = Yii::getAlias('@web') . '/' . ltrim($al->coverAsset->path, '/');
                }

                $artistName = 'Unknown artist';
                if ($al->artist) {
                    $artistName = $al->artist->stage_name ?? 'Unknown artist';
                }
                ?>

                <a class="feat-card" href="<?= Url::to(['album/view', 'id' => $al->id]) ?>">
                    <img class="feat-cover"
                         src="<?= Html::encode($coverUrl) ?>"
                         alt=""
                         style="width:64px;height:64px;border-radius:14px;object-fit:cover;"
                         onerror="this.src='<?= Html::encode($defaultCover) ?>'">

                    <div>
                        <div class="feat-type">ALBUM</div>
                        <div class="feat-title"><?= Html::encode($al->title ?? 'Untitled') ?></div>
                        <div class="feat-sub"><?= Html::encode($artistName) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>



    <!-- ARTISTS tab -->
    <?php if ($type === 'artists'): ?>
    <div class="search-section">
        <div class="search-section-title">Artists</div>
        <?php $artists = $results['artists'] ?? []; ?>

        <?php if (empty($artists)): ?>
            <div class="search-empty" style="padding: 12px 14px;">Sem artistas.</div>
        <?php else: ?>
            <div class="featuring-grid">
                <?php foreach ($artists as $a): ?>
                    <?php
                    $avatarPath =
                        ($safe($a, 'user.profileAsset.path'))
                        ?: ($safe($a, 'profileAsset.path'))
                        ?: null;

                    $avatarUrl = $toWebUrl($avatarPath, $defaultAvatar);
                    $artistName = $a->stage_name ?? $a->artist_name ?? 'Unknown artist';
                    ?>
                    <a class="feat-card" href="<?= Url::to(['artist/view', 'id' => $a->id]) ?>">
                        <img src="<?= Html::encode($avatarUrl) ?>" alt=""
                             width="64" height="64"
                             style="border-radius:14px;object-fit:cover;"
                             onerror="this.src='<?= Html::encode($defaultAvatar) ?>'">
                        <div>
                            <div class="feat-type">ARTIST</div>
                            <div class="feat-title"><?= Html::encode($artistName) ?></div>
                            <div class="feat-sub">By PHONYX</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<?php
$js = <<<JS
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.js-play');
  if (!btn) return;

  const row = btn.closest('.song-row');
  if (!row) return;

  const src = row.getAttribute('data-audio-src');
  if (!src) return;

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
