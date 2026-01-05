<?php
/** @var yii\web\View $this */
/** @var \common\models\Genre[] $genres */
/** @var \common\models\Genre|null $selectedGenre */
/** @var \yii\data\ActiveDataProvider|null $dataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Tracks | PHONYX';

$this->registerCssFile(
    Yii::getAlias('@web/css/tracks.css'),
    ['depends' => [\frontend\assets\AppAsset::class]]
);
?>

<div class="tracks-page">

    <!-- Page header -->
    <header class="tracks-header">
        <h1 class="tracks-title">Todas as faixas</h1>
        <p class="tracks-subtitle">Explora as músicas da PHONYX por género.</p>
    </header>

    <?php if (empty($genres)): ?>
        <p class="tracks-empty">Ainda não existem géneros.</p>
    <?php else: ?>

        <!-- Genre tabs -->
        <nav class="tracks-genres-nav" style="margin-bottom:16px;">
            <div class="tracks-genres-row" style="display:flex; gap:10px; flex-wrap:wrap;">
                <?php foreach ($genres as $g): ?>
                    <?php
                        $isActive = $selectedGenre && ((int)$selectedGenre->id === (int)$g->id);
                        $url = Url::to(['track/index', 'genre' => (int)$g->id, 'p' => 1]);
                    ?>
                    <a href="<?= $url ?>"
                       class="tracks-genre-chip <?= $isActive ? 'is-active' : '' ?>"
                       style="
                        padding:10px 14px;
                        border-radius:999px;
                        background: <?= $isActive ? 'rgba(255,165,51,0.16)' : 'rgba(255,255,255,0.06)' ?>;
                        color: <?= $isActive ? '#ffa533' : 'rgba(255,255,255,0.85)' ?>;
                        border: 1px solid <?= $isActive ? 'rgba(255,165,51,0.35)' : 'rgba(255,255,255,0.08)' ?>;
                        text-decoration:none;
                       ">
                        <?= Html::encode($g->name) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>

        <?php
            $tracks = $dataProvider ? $dataProvider->getModels() : [];
        ?>

        <section class="tracks-genre-block">
            <!-- Genre header -->
            <div class="tracks-genre-header">
                <h2 class="tracks-genre-name">
                    <?= Html::encode($selectedGenre ? $selectedGenre->name : 'Género') ?>
                </h2>
            </div>

            <?php if (empty($tracks)): ?>
                <p class="tracks-empty">Nenhuma faixa neste género.</p>
            <?php else: ?>
                <div class="tracks-list">
                    <?php foreach ($tracks as $track): ?>
                        <?php
                        // Track URLs
                        $trackUrl = Url::to(['track/view', 'id' => $track->id]);

                        // Cover 
                        $coverUrl = Yii::getAlias('@web') . '/img/default-cover.png';

                        // Artist name 
                        $artistName = $track->artist
                            ? ($track->artist->artist_name ?? $track->artist->stage_name ?? 'Unknown artist')
                            : 'Unknown artist';

                        // Audio URL
                        $audioUrl = null;
                        if ($track->audioAsset && !empty($track->audioAsset->path)) {
                            $path = $track->audioAsset->path;
                            $audioUrl = (strpos($path, 'http') === 0) ? $path : (Yii::getAlias('@web') . $path);
                        }

                      
                        $duration = $track->duration ?? null;
                        ?>

                        <article class="tracks-item">
                            <!-- Left-->
                            <a class="tracks-cover" href="<?= $trackUrl ?>">
                                <img src="<?= Html::encode($coverUrl) ?>" alt="">
                            </a>

                            <div class="tracks-info">
                                <a class="tracks-name" href="<?= $trackUrl ?>">
                                    <?= Html::encode($track->title ?? 'Untitled') ?>
                                </a>
                                <div class="tracks-artist"><?= Html::encode($artistName) ?></div>
                            </div>

                            <!-- Right -->
                            <div class="tracks-actions">
                                <?php if (!empty($duration)): ?>
                                    <span class="tracks-duration"><?= Html::encode($duration) ?></span>
                                <?php endif; ?>

                                <button class="tracks-btn tracks-btn--ghost add-to-playlist-btn" type="button"
                                    data-track-id="<?= (int) $track->id ?>">
                                    + Playlist
                                </button>

                                <?php if ($audioUrl): ?>
                                    <button class="tracks-btn tracks-btn--play track-play-btn" type="button"
                                        data-id="<?= (int) $track->id ?>"
                                        data-audio="<?= Html::encode($audioUrl) ?>"
                                        data-title="<?= Html::encode($track->title ?? '') ?>"
                                        data-artist="<?= Html::encode($artistName) ?>"
                                        data-cover="<?= Html::encode($coverUrl) ?>">
                                        ▶
                                    </button>
                                <?php else: ?>
                                    <span class="tracks-no-audio">Sem áudio</span>
                                <?php endif; ?>
                            </div>
                        </article>

                    <?php endforeach; ?>
                </div>

                <!-- Pager -->
                <div class="tracks-pager" style="margin-top:18px; display:flex; justify-content:center;">
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'maxButtonCount' => 7,
                        'options' => ['class' => 'pagination'],
                        'linkOptions' => ['class' => 'page-link'],
                        'pageCssClass' => 'page-item',
                        'activePageCssClass' => 'active',
                        'disabledPageCssClass' => 'disabled',
                        'prevPageLabel' => '‹',
                        'nextPageLabel' => '›',
                    ]) ?>
                </div>

            <?php endif; ?>
        </section>

    <?php endif; ?>

</div>

<?php
$myPlaylistsUrl = Url::to(['playlist/my-playlists']);
$addTrackUrl = Url::to(['playlist/add-track']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->getCsrfToken();

$this->registerJs(<<<JS
(function () {
  let openMenuEl = null;
  let openBtnEl = null;
  let cachedPlaylists = null;
  let loading = false;

  function closeMenu() {
    if (openMenuEl) openMenuEl.remove();
    openMenuEl = null;
    openBtnEl = null;
  }

  function createMenu() {
    const menu = document.createElement('div');
    menu.className = 'pl-dd';

    const header = document.createElement('div');
    header.className = 'pl-dd__header';
    header.innerHTML = '<div class="pl-dd__title">Add to playlist</div><button type="button" class="pl-dd__close" aria-label="Close">×</button>';
    menu.appendChild(header);

    const body = document.createElement('div');
    body.className = 'pl-dd__body';
    body.innerHTML = '<div class="pl-dd__loading">Loading…</div>';
    menu.appendChild(body);

    header.querySelector('.pl-dd__close').addEventListener('click', closeMenu);
    return menu;
  }

  function positionMenu(btn, menu) {
    const r = btn.getBoundingClientRect();
    const gap = 8;

    menu.style.position = 'fixed';
    menu.style.top = (r.bottom + gap) + 'px';
    menu.style.left = Math.min(r.left, window.innerWidth - 340) + 'px';
    menu.style.width = Math.max(260, Math.min(320, r.width + 120)) + 'px';
    menu.style.zIndex = 9999;
  }

  async function fetchPlaylists() {
    if (cachedPlaylists) return cachedPlaylists;
    if (loading) return null;

    loading = true;
    try {
      const res = await fetch('$myPlaylistsUrl', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      if (data && data.success) {
        cachedPlaylists = data.playlists || [];
        return cachedPlaylists;
      }
      cachedPlaylists = [];
      return cachedPlaylists;
    } catch (e) {
      cachedPlaylists = [];
      return cachedPlaylists;
    } finally {
      loading = false;
    }
  }

  async function addToPlaylist(playlistId, trackId) {
    const form = new URLSearchParams();
    form.append('playlist_id', String(playlistId));
    form.append('track_id', String(trackId));
    form.append('$csrfParam', '$csrfToken');

    const res = await fetch('$addTrackUrl', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: form.toString()
    });

    return res.json().catch(() => ({}));
  }

  function renderPlaylists(menu, playlists, trackId) {
    const body = menu.querySelector('.pl-dd__body');
    body.innerHTML = '';

    if (!playlists || playlists.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'pl-dd__empty';
      empty.textContent = 'You have no playlists yet.';
      body.appendChild(empty);
      return;
    }

    const list = document.createElement('div');
    list.className = 'pl-dd__list';

    playlists.forEach(p => {
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'pl-dd__item';
      item.innerHTML = '<span class="pl-dd__itemTitle"></span><span class="pl-dd__itemIcon">+</span>';
      item.querySelector('.pl-dd__itemTitle').textContent = p.title;

      item.addEventListener('click', async () => {
        item.disabled = true;
        item.classList.add('is-loading');

        const result = await addToPlaylist(p.id, trackId);

        item.classList.remove('is-loading');

        if (result && result.success) {
          item.classList.add('is-done');
          item.querySelector('.pl-dd__itemIcon').textContent = '✓';
          setTimeout(closeMenu, 450);
          return;
        }

        item.disabled = false;
        alert('Failed to add track to playlist.');
      });

      list.appendChild(item);
    });

    body.appendChild(list);
  }

  document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.add-to-playlist-btn');

    if (!btn) {
      if (openMenuEl && !e.target.closest('.pl-dd')) closeMenu();
      return;
    }

    const trackId = parseInt(btn.dataset.trackId || '0', 10);
    if (!trackId) return;

    if (openBtnEl === btn) {
      closeMenu();
      return;
    }

    closeMenu();

    openBtnEl = btn;
    openMenuEl = createMenu();
    document.body.appendChild(openMenuEl);
    positionMenu(btn, openMenuEl);

    const playlists = await fetchPlaylists();
    renderPlaylists(openMenuEl, playlists, trackId);
  });

  window.addEventListener('resize', () => {
    if (openBtnEl && openMenuEl) positionMenu(openBtnEl, openMenuEl);
  });

  window.addEventListener('scroll', () => {
    if (openBtnEl && openMenuEl) positionMenu(openBtnEl, openMenuEl);
  }, true);
})();
JS);

$this->registerJs(<<<JS
(function(){
  document.addEventListener('click', function(e){
    const playBtn = e.target.closest('.track-play-btn');
    if (!playBtn) return;

    e.preventDefault();
    e.stopPropagation();

    const src = playBtn.dataset.audio || '';
    if (!src) {
      console.log('[PLAY] botão sem data-audio');
      return;
    }

    if (typeof window.phonyxSetTrack !== 'function') {
      console.log('[PLAY] window.phonyxSetTrack não existe');
      try {
        if (!window.__testAudio) window.__testAudio = new Audio();
        window.__testAudio.src = src;
        window.__testAudio.play().catch(()=>{});
      } catch (err) {}
      return;
    }

    window.phonyxSetTrack({
      id: playBtn.dataset.id || '',
      src: src,
      title: playBtn.dataset.title || '',
      artist: playBtn.dataset.artist || '',
      cover: playBtn.dataset.cover || ''
    });
  });
})();
JS, \yii\web\View::POS_END);
?>
