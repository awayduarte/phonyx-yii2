<?php
use yii\helpers\Html;
use yii\helpers\Url;

// Like route
$toggleLikeUrl = Url::to(['playlist/toggle-like']);
$csrf          = Yii::$app->request->csrfToken;
$isGuest       = Yii::$app->user->isGuest ? '1' : '0';

// Default track (home)
$defaultSrc    = Yii::getAlias('@web/media/disstrack-albert.mp3');
$defaultTitle  = 'Disstrack Albert';
$defaultArtist = 'Phonyx · Single';
?>

<section class="player-shell" id="player-shell"
         data-track-id=""
         data-is-guest="<?= Html::encode($isGuest) ?>"
         data-default-src="<?= Html::encode($defaultSrc) ?>"
         data-default-title="<?= Html::encode($defaultTitle) ?>"
         data-default-artist="<?= Html::encode($defaultArtist) ?>">

    <div class="player-bar">
        <!-- LEFT -->
        <div class="player-left">
            <div class="player-cover"></div>
            <div class="player-meta">
                <div class="player-title"><?= Html::encode($defaultTitle) ?></div>
                <div class="player-artist"><?= Html::encode($defaultArtist) ?></div>
            </div>
        </div>

        <!-- CENTER -->
        <div class="player-center">
            <div class="player-controls">
                <button class="player-btn player-small" type="button" id="player-prev">⏮</button>
                <button class="player-btn player-main" type="button" id="player-play">
                    <span id="player-play-icon">▶</span>
                </button>
                <button class="player-btn player-small" type="button" id="player-next">⏭</button>
            </div>

            <div class="player-timeline">
                <span class="player-time" id="player-current-time">0:00</span>
                <div class="player-progress" id="player-progress-bar">
                    <div class="player-progress-fill" id="player-progress-fill"></div>
                </div>
                <span class="player-time" id="player-duration">0:00</span>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="player-right">
            <button type="button"
                    id="player-like-btn"
                    class="player-like-btn"
                    title="Add to likes">
                ♡
            </button>

            <span class="player-volume-icon">🔊</span>
            <input type="range" min="0" max="1" step="0.01" value="0.7" id="player-volume">
        </div>

        <button class="player-toggle" type="button" id="player-toggle">▾</button>
    </div>

    <audio id="phonyx-audio"></audio>
</section>

<?php
$this->registerJs(<<<JS
(function () {
  const audio         = document.getElementById('phonyx-audio');
  const shell         = document.getElementById('player-shell');
  if (!audio || !shell) return;

  const playBtn       = document.getElementById('player-play');
  const playIcon      = document.getElementById('player-play-icon');
  const progressBar   = document.getElementById('player-progress-bar');
  const progressFill  = document.getElementById('player-progress-fill');
  const currentTimeEl = document.getElementById('player-current-time');
  const durationEl    = document.getElementById('player-duration');
  const volumeSlider  = document.getElementById('player-volume');
  const toggleBtn     = document.getElementById('player-toggle');
  const likeBtn       = document.getElementById('player-like-btn');
  const prevBtn       = document.getElementById('player-prev');
  const nextBtn       = document.getElementById('player-next');

  const titleEl       = shell.querySelector('.player-title');
  const artistEl      = shell.querySelector('.player-artist');
  const coverEl       = shell.querySelector('.player-cover');

  const csrfToken     = '$csrf';
  const toggleLikeUrl = '$toggleLikeUrl';
  const isGuest       = shell.dataset.isGuest === '1';

  // Global player state
  window.phonyxPlayer = window.phonyxPlayer || {};
  const P = window.phonyxPlayer;
  P.audio = audio;
  P.queue = Array.isArray(P.queue) ? P.queue : [];
  P.queueIndex = typeof P.queueIndex === 'number' ? P.queueIndex : -1;
  P.currentId = P.currentId || '';

  function formatTime(sec) {
    if (!sec || isNaN(sec)) return '0:00';
    const minutes = Math.floor(sec / 60);
    const seconds = Math.floor(sec % 60).toString().padStart(2, '0');
    return minutes + ':' + seconds;
  }

  function updatePlayIcon() {
    if (!playIcon) return;
    playIcon.textContent = audio.paused ? '▶' : '❚❚';
  }

  function setLikeState(isLiked) {
    if (!likeBtn) return;
    likeBtn.classList.toggle('is-liked', !!isLiked);
    likeBtn.textContent = isLiked ? '♥' : '♡';
  }

  function dispatch(name) {
    try { window.dispatchEvent(new CustomEvent(name)); } catch(e) {}
  }

  // ====== AUDIO EVENTS ======
  audio.addEventListener('loadedmetadata', function () {
    if (durationEl) durationEl.textContent = formatTime(audio.duration);
  });

  audio.addEventListener('timeupdate', function () {
    if (currentTimeEl) currentTimeEl.textContent = formatTime(audio.currentTime);
    if (progressFill && audio.duration) {
      const percent = (audio.currentTime / audio.duration) * 100;
      progressFill.style.width = (percent || 0) + '%';
    }
  });

  audio.addEventListener('play', function () {
    updatePlayIcon();
    dispatch('phonyx:play');
  });

  audio.addEventListener('pause', function () {
    updatePlayIcon();
    dispatch('phonyx:pause');
  });

  // Auto-next on end
  audio.addEventListener('ended', function () {
    if (typeof window.phonyxNext === 'function') {
      const ok = window.phonyxNext();
      if (!ok) audio.pause();
    }
  });

  // ====== CONTROLS ======
  if (playBtn) {
    playBtn.addEventListener('click', function () {
      if (!audio.src) return;
      if (audio.paused) audio.play().catch(console.error);
      else audio.pause();
    });
  }

  if (progressBar) {
    progressBar.addEventListener('click', function (e) {
      if (!audio.duration) return;
      const rect = progressBar.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const percent = clickX / rect.width;
      audio.currentTime = audio.duration * percent;
    });
  }

  if (volumeSlider) {
    volumeSlider.addEventListener('input', function () {
      audio.volume = parseFloat(this.value || 0.7);
    });
    audio.volume = parseFloat(volumeSlider.value || 0.7);
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      shell.classList.toggle('collapsed');
      toggleBtn.textContent = shell.classList.contains('collapsed') ? '▴' : '▾';
    });
  }

  // Prev/Next buttons
  if (prevBtn) prevBtn.addEventListener('click', function () {
    if (typeof window.phonyxPrev === 'function') window.phonyxPrev();
  });

  if (nextBtn) nextBtn.addEventListener('click', function () {
    if (typeof window.phonyxNext === 'function') window.phonyxNext();
  });

  // ====== QUEUE API ======
  window.phonyxSetQueue = function (tracks, startId) {
    P.queue = Array.isArray(tracks) ? tracks.filter(t => t && t.src) : [];
    P.queueIndex = -1;

    if (P.queue.length) {
      if (startId) {
        const idx = P.queue.findIndex(t => String(t.id) === String(startId));
        P.queueIndex = idx >= 0 ? idx : 0;
      } else {
        P.queueIndex = 0;
      }
    }
  };

  window.phonyxNext = function () {
    if (!P.queue || !P.queue.length) return false;
    if (P.queueIndex < 0) P.queueIndex = 0;
    P.queueIndex = (P.queueIndex + 1) % P.queue.length;

    const t = P.queue[P.queueIndex];
    if (!t) return false;

    window.phonyxSetTrack({ ...t, autoplay: true });
    return true;
  };

  window.phonyxPrev = function () {
    if (P.audio && P.audio.currentTime > 3) {
      P.audio.currentTime = 0;
      return true;
    }

    if (!P.queue || !P.queue.length) return false;
    if (P.queueIndex < 0) P.queueIndex = 0;
    P.queueIndex = (P.queueIndex - 1 + P.queue.length) % P.queue.length;

    const t = P.queue[P.queueIndex];
    if (!t) return false;

    window.phonyxSetTrack({ ...t, autoplay: true });
    return true;
  };

  // Optional helper: toggle play only if same track
  window.phonyxTogglePlay = function (trackId) {
    const currentId = String(P.currentId || '');
    const wantId = String(trackId || '');
    if (!currentId || !wantId || currentId !== wantId) return false;

    if (audio.paused) audio.play().catch(console.error);
    else audio.pause();
    return true;
  };

  // ====== LIKES ======
  if (likeBtn) {
    likeBtn.addEventListener('click', function () {
      const trackId = shell.dataset.trackId;
      if (!trackId) return;

      if (isGuest) {
        alert('You need to login to like tracks.');
        return;
      }

      fetch(toggleLikeUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: 'track_id=' + encodeURIComponent(trackId) + '&_csrf=' + encodeURIComponent(csrfToken)
      })
      .then(r => r.json())
      .then(data => {
        if (!data || !data.success) return;
        setLikeState(data.state === 'added');
      })
      .catch(console.error);
    });
  }

  // ====== GLOBAL SET TRACK ======
  window.phonyxSetTrack = function (opts) {
    if (!opts || !opts.src) return;

    audio.src = opts.src;
    audio.currentTime = 0;

    if (opts.autoplay === false) audio.pause();
    else audio.play().catch(console.error);

    if (titleEl) titleEl.textContent = (opts.title || '');
    if (artistEl) artistEl.textContent = (opts.artist || '');

    if (coverEl) {
      if (opts.cover) {
        coverEl.style.backgroundImage    = 'url(' + opts.cover + ')';
        coverEl.style.backgroundSize     = 'cover';
        coverEl.style.backgroundRepeat   = 'no-repeat';
        coverEl.style.backgroundPosition = 'center';
      } else {
        coverEl.style.backgroundImage = '';
      }
    }

    // Track id
    const tid = (opts.trackId !== undefined && opts.trackId !== null && String(opts.trackId) !== '')
      ? String(opts.trackId)
      : String(opts.id || '');

    shell.dataset.trackId = tid;
    P.currentId = tid;

    // Sync queueIndex if exists
    if (P.queue && P.queue.length && tid) {
      const idx = P.queue.findIndex(t => String(t.id) === tid);
      if (idx >= 0) P.queueIndex = idx;
    }

    // Like state
    setLikeState(!!opts.isLiked);

    shell.classList.remove('collapsed');
    updatePlayIcon();

    dispatch('phonyx:trackchange');
  };

  // ====== DEFAULT TRACK ON LOAD ======
  const defSrc    = shell.dataset.defaultSrc;
  const defTitle  = shell.dataset.defaultTitle;
  const defArtist = shell.dataset.defaultArtist;

  if (defSrc) {
    window.phonyxSetTrack({
      src: defSrc,
      title: defTitle || '',
      artist: defArtist || '',
      cover: '',
      trackId: '',
      isLiked: false,
      autoplay: false
    });
  }
})();
JS);
?>
