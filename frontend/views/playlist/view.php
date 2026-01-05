<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var \common\models\Playlist $playlist */
/** @var \common\models\Track[] $tracks */

$this->title = $playlist->title;

$this->registerCssFile('@web/css/playlists.css?v=' . time(), [
  'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

$addUrl    = Url::to(['playlist/add-track']);
$removeUrl = Url::to(['playlist/remove-track']);
$searchUrl = Url::to(['track/search'], true);

/* ===================== PLAYLIST COVER ===================== */
$coverPath = null;
if (!empty($playlist->cover_asset_id)) {
  $coverAsset = \common\models\Asset::findOne((int)$playlist->cover_asset_id);
  if ($coverAsset) $coverPath = (string)$coverAsset->path;
}

$coverSrc = null;
if (!empty($coverPath)) {
  if (preg_match('~^https?://~i', $coverPath)) {
    $coverSrc = $coverPath;
  } else {
    $p = $coverPath;
    if ($p !== '' && $p[0] !== '/') $p = '/' . $p;
    $coverSrc = Url::to('@web' . $p, true);
  }
}

$coverBust = '?v=' . time();

/* ===================== PLAYLIST LIKE ===================== */
$likeUrl   = Url::to(['playlist/like', 'id' => $playlist->id]);
$unlikeUrl = Url::to(['playlist/unlike', 'id' => $playlist->id]);
$loginUrl  = Url::to(['site/login'], true);

$isLiked = false;
$likeCount = (int)(new \yii\db\Query())
  ->from('{{%playlist_like}}')
  ->where(['playlist_id' => (int)$playlist->id])
  ->count();

if (!Yii::$app->user->isGuest) {
  $isLiked = (new \yii\db\Query())
    ->from('{{%playlist_like}}')
    ->where([
      'playlist_id' => (int)$playlist->id,
      'user_id' => (int)Yii::$app->user->id,
    ])
    ->exists();
}

/* ===================== QUEUE (player) ===================== */
$queue = [];
foreach ($tracks as $t) {
  $audioPath = $t->audioAsset->path ?? null;
  if (!$audioPath) continue;

  $artistName = '';
  if (isset($t->artist)) {
    $artistName = $t->artist->stage_name ?? ($t->artist->name ?? '');
  }

  // NOTE: some projects don't have track cover relation; keep safe
  $trackCoverPath = null;
  if (isset($t->coverAsset) && $t->coverAsset) {
    $trackCoverPath = $t->coverAsset->path ?? null;
  }

  $ap = (string)$audioPath;
  if ($ap !== '' && $ap[0] !== '/') $ap = '/' . $ap;
  $audioUrl = Url::to('@web' . $ap, true);

  $coverUrlTrack = Url::to('@web/img/default-cover.png', true);
  if (!empty($trackCoverPath)) {
    $cp = (string)$trackCoverPath;
    if ($cp !== '' && $cp[0] !== '/') $cp = '/' . $cp;
    $coverUrlTrack = Url::to('@web' . $cp, true);
  }

  $queue[] = [
    'id'     => (int)$t->id,
    'title'  => (string)$t->title,
    'artist' => (string)$artistName,
    'src'    => (string)$audioUrl,
    'cover'  => (string)$coverUrlTrack,
  ];
}

$hasQueue = !empty($queue);
$defaultCoverSearch = Url::to('@web/img/default-cover.png', true);
?>

<div class="playlist-page">

  <div class="playlist-hero">
    <div class="playlist-hero-inner container">

      <button class="cover-btn" id="btnCover" type="button" title="Mudar capa">
        <?php if ($coverSrc): ?>
          <img src="<?= Html::encode($coverSrc . $coverBust) ?>" alt="Capa">
        <?php else: ?>
          <div class="cover-placeholder">♪</div>
        <?php endif; ?>

        <div class="cover-overlay">
          <div class="cover-icon">✎</div>
          <div>Escolher foto</div>
        </div>
      </button>

      <div class="playlist-hero-text">
        <div class="playlist-type">Playlist</div>
        <h1 class="playlist-title"><?= Html::encode($playlist->title) ?></h1>

        <?php if ($playlist->description): ?>
          <div class="playlist-desc"><?= Html::encode($playlist->description) ?></div>
        <?php endif; ?>

        <div class="playlist-hero-cta">
          <button class="btn-play-big" type="button" id="btnPlayPlaylist" <?= $hasQueue ? '' : 'disabled' ?>>▶</button>
          <button class="btn-icon" type="button" id="btnShuffle" <?= $hasQueue ? '' : 'disabled' ?>>🔀</button>

          <!-- Like / Unlike playlist -->
          <button
            class="btn-icon btn-like-playlist <?= $isLiked ? 'is-liked' : '' ?>"
            type="button"
            id="btnLikePlaylist"
            data-liked="<?= $isLiked ? '1' : '0' ?>"
            title="<?= $isLiked ? 'Liked' : 'Like' ?>"
          >
            <span class="like-heart"><?= $isLiked ? '♥' : '♡' ?></span>
            <span class="like-count" id="likeCount"><?= (int)$likeCount ?></span>
          </button>

          <button class="btn-icon" type="button" id="btnAddCurrent" title="Adicionar música a tocar">＋</button>
        </div>

      </div>

    </div>
  </div>

  <div class="container playlist-body py-4">

    <?php if (Yii::$app->session->hasFlash('error')): ?>
      <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
      <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <div class="pl-find-box" id="findBox">
      <div class="pl-find-header">
        <h2 class="pl-find-title">Vamos encontrar algo para a tua playlist</h2>
        <button type="button" class="pl-find-close" id="btnCloseFind" title="Fechar">✕</button>
      </div>

      <div class="pl-find-search">
        <span class="pl-find-icon">🔍</span>
        <input id="trackQuery" class="pl-find-input" placeholder="Procurar músicas..." autocomplete="off">
        <button type="button" class="pl-find-clear" id="btnClearFind" title="Limpar">✕</button>
      </div>

      <div id="trackResults" class="pl-find-results"></div>
    </div>

    <div class="tracks-head">
      <h3 class="mb-0">Músicas</h3>
    </div>

    <?php if (empty($tracks)): ?>
      <div class="empty-box">Ainda não tens músicas nesta playlist.</div>
    <?php else: ?>
      <div class="track-list">
        <?php foreach ($tracks as $i => $t): ?>
          <?php
            $artistName = '';
            if (isset($t->artist)) {
              $artistName = $t->artist->stage_name ?? ($t->artist->name ?? '');
            }
          ?>
          <div class="track-row">
            <div class="track-idx"><?= (int)$i + 1 ?></div>

            <div class="track-main">
              <div class="track-name"><?= Html::encode($t->title) ?></div>
              <div class="track-sub"><?= Html::encode($artistName) ?></div>
            </div>

            <div class="track-actions">
              <button class="btnTrackPlay" type="button" data-track-id="<?= (int)$t->id ?>" title="Tocar">▶</button>
              <button class="btnTrackRemove btnRemove" type="button" data-track-id="<?= (int)$t->id ?>">Remover</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form id="coverForm"
          action="<?= Url::to(['playlist/update-cover', 'id' => $playlist->id]) ?>"
          method="post"
          enctype="multipart/form-data"
          style="display:none;">
      <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
      <input type="file" name="cover" id="coverInput" accept="image/*">
    </form>

  </div>
</div>

<script>
const PLAYLIST_ID = <?= (int)$playlist->id ?>;
const ADD_URL     = <?= json_encode($addUrl) ?>;
const REMOVE_URL  = <?= json_encode($removeUrl) ?>;
const SEARCH_URL  = <?= json_encode($searchUrl) ?>;
const QUEUE       = <?= json_encode($queue, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>;
const DEFAULT_COVER = <?= json_encode($defaultCoverSearch) ?>;

const LIKE_URL   = <?= json_encode($likeUrl) ?>;
const UNLIKE_URL = <?= json_encode($unlikeUrl) ?>;
const LOGIN_URL  = <?= json_encode($loginUrl) ?>;
const IS_GUEST   = <?= Yii::$app->user->isGuest ? 'true' : 'false' ?>;

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function postJson(url, data) {
  return fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'X-CSRF-Token': getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: new URLSearchParams(data).toString()
  }).then(async r => {
    const txt = await r.text();
    try { return JSON.parse(txt); }
    catch(e) { console.log('RAW:', txt); return {ok:false, success:false}; }
  });
}

function escapeHtml(str) {
  return String(str ?? '')
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;')
    .replaceAll('"','&quot;')
    .replaceAll("'","&#039;");
}

function shuffleArray(arr){
  const a = arr.slice();
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

/* ========= PLAYER INTEGRATION ========= */
const audioEl = document.getElementById('phonyx-audio');
let plQueue = QUEUE.slice();
let plIndex = 0;

function setHeroPlayIcon(isPlaying){
  const b = document.getElementById('btnPlayPlaylist');
  if (!b) return;
  b.textContent = isPlaying ? '❚❚' : '▶';
}

if (audioEl) {
  audioEl.addEventListener('play', () => setHeroPlayIcon(true));
  audioEl.addEventListener('pause', () => setHeroPlayIcon(false));
  audioEl.addEventListener('ended', () => {
    if (!plQueue.length) return;
    plIndex = (plIndex + 1) % plQueue.length;
    playAt(plIndex, true);
  });
}

function playAt(index, autoplay=true){
  if (!plQueue.length) return;
  plIndex = Math.max(0, Math.min(index, plQueue.length - 1));
  const t = plQueue[plIndex];

  if (typeof window.phonyxSetTrack === 'function') {
    window.phonyxSetTrack({
      src: t.src,
      title: t.title,
      artist: t.artist,
      cover: t.cover,
      trackId: t.id,
      isLiked: false,
      autoplay: autoplay
    });
    window.currentTrackId = t.id;
    return;
  }

  if (audioEl) {
    audioEl.src = t.src;
    audioEl.currentTime = 0;
    window.currentTrackId = t.id;
    if (autoplay) audioEl.play().catch(()=>{});
  }
}

function togglePlayPause(){
  if (!audioEl) return;
  if (!audioEl.src && plQueue.length) playAt(0, true);
  else if (audioEl.paused) audioEl.play().catch(()=>{});
  else audioEl.pause();
}

function playTrackById(id){
  const idx = QUEUE.findIndex(x => x.id === parseInt(id, 10));
  if (idx >= 0) {
    plQueue = QUEUE.slice();
    playAt(idx, true);
  }
}

const btnPrev = document.getElementById('player-prev');
const btnNext = document.getElementById('player-next');

if (btnPrev) btnPrev.addEventListener('click', () => {
  if (!plQueue.length) return;
  plIndex = (plIndex - 1 + plQueue.length) % plQueue.length;
  playAt(plIndex, true);
});
if (btnNext) btnNext.addEventListener('click', () => {
  if (!plQueue.length) return;
  plIndex = (plIndex + 1) % plQueue.length;
  playAt(plIndex, true);
});

/* HERO BUTTONS */
const btnPlay = document.getElementById('btnPlayPlaylist');
if (btnPlay) btnPlay.addEventListener('click', () => togglePlayPause());

const btnShuffle = document.getElementById('btnShuffle');
if (btnShuffle) btnShuffle.addEventListener('click', () => {
  plQueue = shuffleArray(QUEUE);
  playAt(0, true);
});

/* LIKE PLAYLIST */
const btnLikePlaylist = document.getElementById('btnLikePlaylist');
if (btnLikePlaylist) {
  btnLikePlaylist.addEventListener('click', async () => {
    if (IS_GUEST) { window.location.href = LOGIN_URL; return; }

    const liked = btnLikePlaylist.dataset.liked === '1';
    const url = liked ? UNLIKE_URL : LIKE_URL;

    btnLikePlaylist.disabled = true;
    try {
      const res = await postJson(url, {});
      if (!res || !res.ok) return;

      const newLiked = !!res.liked;
      btnLikePlaylist.dataset.liked = newLiked ? '1' : '0';
      btnLikePlaylist.classList.toggle('is-liked', newLiked);

      const heart = btnLikePlaylist.querySelector('.like-heart');
      const count = document.getElementById('likeCount');
      if (heart) heart.textContent = newLiked ? '♥' : '♡';
      if (count && typeof res.count !== 'undefined') count.textContent = String(res.count);

    } catch (e) {
      console.log(e);
    } finally {
      btnLikePlaylist.disabled = false;
    }
  });
}

/* ADD CURRENT */
const btnAddCurrent = document.getElementById('btnAddCurrent');
if (btnAddCurrent) btnAddCurrent.addEventListener('click', async () => {
  const currentTrackId = window.currentTrackId || null;
  if (!currentTrackId) return;
  const res = await postJson(ADD_URL, { playlist_id: PLAYLIST_ID, track_id: currentTrackId });
  if (res.success) location.reload();
});

/* COVER UPLOAD */
document.getElementById('btnCover')?.addEventListener('click', () => {
  document.getElementById('coverInput')?.click();
});
document.getElementById('coverInput')?.addEventListener('change', () => {
  if (document.getElementById('coverInput').files.length) {
    document.getElementById('coverForm').submit();
  }
});

/* REMOVE TRACK */
document.querySelectorAll('.btnRemove').forEach(btn => {
  btn.addEventListener('click', async () => {
    const trackId = btn.dataset.trackId;
    const res = await postJson(REMOVE_URL, { playlist_id: PLAYLIST_ID, track_id: trackId });
    if (res.success) location.reload();
  });
});

/* PLAY PER TRACK */
document.querySelectorAll('.btnTrackPlay').forEach(btn => {
  btn.addEventListener('click', () => playTrackById(btn.dataset.trackId));
});

/* SEARCH */
const resultsBox = document.getElementById('trackResults');
const queryInput = document.getElementById('trackQuery');
const btnClear   = document.getElementById('btnClearFind');
const btnClose   = document.getElementById('btnCloseFind');
const findBox    = document.getElementById('findBox');

let searchTimer = null;

function renderResults(items) {
  if (!items || !items.length) {
    resultsBox.innerHTML = `<div class="plr-empty">Sem resultados.</div>`;
    return;
  }

  resultsBox.innerHTML = items.map(it => `
    <div class="plr-row">
      <img class="plr-cover" src="${escapeHtml(it.cover || DEFAULT_COVER)}" alt="">
      <div class="plr-meta">
        <div class="plr-title">${escapeHtml(it.title)}</div>
        <div class="plr-sub">${escapeHtml(it.artist || it.subtitle || '')}</div>
      </div>
      <button class="plr-btn btnAddResult" data-id="${it.id}" type="button">Adicionar</button>
    </div>
  `).join('');

  resultsBox.querySelectorAll('.btnAddResult').forEach(btn => {
    btn.addEventListener('click', async () => {
      const trackId = btn.dataset.id;
      btn.disabled = true;
      const res = await postJson(ADD_URL, { playlist_id: PLAYLIST_ID, track_id: trackId });
      if (res.success) location.reload();
      btn.disabled = false;
    });
  });
}

async function doSearch(q) {
  const url = new URL(SEARCH_URL);
  url.searchParams.set('q', q);
  const r = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  const items = await r.json();
  renderResults(items);
}

queryInput?.addEventListener('input', () => {
  const q = queryInput.value.trim();
  clearTimeout(searchTimer);
  if (q.length < 2) { resultsBox.innerHTML = ''; return; }
  searchTimer = setTimeout(() => doSearch(q), 220);
});

btnClear?.addEventListener('click', () => {
  queryInput.value = '';
  resultsBox.innerHTML = '';
  queryInput.focus();
});

btnClose?.addEventListener('click', () => {
  findBox.remove();
});
</script>
