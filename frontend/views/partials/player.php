<?php
use yii\helpers\Html;
use yii\helpers\Url;

// Like route
$toggleLikeUrl = Url::to(['playlist/toggle-like']);
$csrf          = Yii::$app->request->csrfToken;

// Default track
$defaultSrc    = Yii::getAlias('@web/media/disstrack-albert.mp3');
$defaultTitle  = 'Disstrack Albert';
$defaultArtist = 'Phonyx · Single';
?>

<section class="player-shell" id="player-shell"
         data-track-id=""
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
                    title="Add to Likes">
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
  const audio = document.getElementById('phonyx-audio');
  const shell = document.getElementById('player-shell');
  if (!audio || !shell) return;

  const playBtn       = document.getElementById('player-play');
  const playIcon      = document.getElementById('player-play-icon');
  const prevBtn       = document.getElementById('player-prev');
  const nextBtn       = document.getElementById('player-next');
  const progressBar   = document.getElementById('player-progress-bar');
  const progressFill  = document.getElementById('player-progress-fill');
  const currentTimeEl = document.getElementById('player-current-time');
  const durationEl    = document.getElementById('player-duration');
  const volumeSlider  = document.getElementById('player-volume');
  const toggleBtn     = document.getElementById('player-toggle');
  const likeBtn       = document.getElementById('player-like-btn');

  const titleEl  = shell.querySelector('.player-title');
  const artistEl = shell.querySelector('.player-artist');
  const coverEl  = shell.querySelector('.player-cover');

  const csrfToken     = '$csrf';
  const toggleLikeUrl = '$toggleLikeUrl';

  // Global player state
  const state = {
    audio,
    queue: [],
    currentIndex: -1,
    currentId: '',
  };

  window.phonyxPlayer = state;

  function emit(name) {
    window.dispatchEvent(new Event(name));
  }

  function formatTime(sec) {
    if (!sec || isNaN(sec)) return '0:00';
    const m = Math.floor(sec / 60);
    const s = Math.floor(sec % 60).toString().padStart(2,'0');
    return m + ':' + s;
  }

  function updatePlayIcon() {
    if (!playIcon) return;
    playIcon.textContent = audio.paused ? '▶' : '❚❚';
  }

  function setLikeState(isLiked) {
    if (!likeBtn) return;
    if (isLiked) {
      likeBtn.classList.add('is-liked');
      likeBtn.textContent = '♥';
    } else {
      likeBtn.classList.remove('is-liked');
      likeBtn.textContent = '♡';
    }
  }

  function getQueueItemById(id) {
    const idx = state.queue.findIndex(t => String(t.id) === String(id));
    if (idx === -1) return { idx: -1, item: null };
    return { idx, item: state.queue[idx] };
  }

  function applyMeta(opts) {
    if (titleEl)  titleEl.textContent  = opts.title || '';
    if (artistEl) artistEl.textContent = opts.artist || '';
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
  }

  function setCurrentById(id) {
    const found = getQueueItemById(id);
    state.currentIndex = found.idx;
    state.currentId = String(id || '');
    shell.dataset.trackId = String(id || '');
    setLikeState(!!(found.item && found.item.isLiked));
  }

  // ===== Audio events =====
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

  audio.addEventListener('play', function(){
    updatePlayIcon();
    emit('phonyx:play');
  });

  audio.addEventListener('pause', function(){
    updatePlayIcon();
    emit('phonyx:pause');
  });

  audio.addEventListener('ended', function(){
    // Auto-next
    window.phonyxNext();
  });

  // ===== Controls =====
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
      const x = e.clientX - rect.left;
      audio.currentTime = audio.duration * (x / rect.width);
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

  // ===== Like =====
  if (likeBtn) {
    likeBtn.addEventListener('click', function () {
      const trackId = shell.dataset.trackId;
      if (!trackId) return;

      fetch(toggleLikeUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: 'track_id=' + encodeURIComponent(trackId) + '&_csrf=' + encodeURIComponent(csrfToken)
      })
      .then(r => r.json())
      .then(data => {
        if (!data || !data.success) return;

        const liked = (data.state === 'added');
        setLikeState(liked);

        // Update queue item state
        const found = getQueueItemById(trackId);
        if (found.item) found.item.isLiked = liked;
      })
      .catch(console.error);
    });
  }

  // ===== Queue API =====
  window.phonyxSetQueue = function(queue, startId) {
    state.queue = Array.isArray(queue) ? queue : [];
    if (startId) {
      const found = getQueueItemById(startId);
      state.currentIndex = found.idx;
      state.currentId = String(startId || '');
    }
  };

  function playByIndex(index) {
    if (!state.queue.length) return;
    if (index < 0 || index >= state.queue.length) return;

    const t = state.queue[index];
    if (!t || !t.src) return;

    state.currentIndex = index;
    state.currentId = String(t.id || '');
    shell.dataset.trackId = state.currentId;

    audio.src = t.src;
    audio.currentTime = 0;

    applyMeta(t);
    setLikeState(!!t.isLiked);

    shell.classList.remove('collapsed');
    audio.play().catch(console.error);

    emit('phonyx:trackchange');
  }

  window.phonyxNext = function() {
    if (state.queue.length && state.currentIndex >= 0) {
      const next = state.currentIndex + 1;
      if (next < state.queue.length) return playByIndex(next);
      // End of queue -> stop (or restart if you want)
      audio.pause();
      audio.currentTime = 0;
      updatePlayIcon();
      return;
    }
    // No queue -> just restart current
    if (audio.src) audio.currentTime = 0;
  };

  window.phonyxPrev = function() {
    // If track has played more than 3s -> restart
    if (audio.currentTime > 3) {
      audio.currentTime = 0;
      return;
    }

    if (state.queue.length && state.currentIndex >= 0) {
      const prev = state.currentIndex - 1;
      if (prev >= 0) return playByIndex(prev);
      // First track -> restart
      audio.currentTime = 0;
      return;
    }

    // No queue
    audio.currentTime = 0;
  };

  if (nextBtn) nextBtn.addEventListener('click', window.phonyxNext);
  if (prevBtn) prevBtn.addEventListener('click', window.phonyxPrev);

  // ===== Toggle play helper =====
  window.phonyxTogglePlay = function(optionalId) {
    const id = String(optionalId || '');
    if (id && state.currentId && id !== state.currentId) return false;

    if (!audio.src) return false;
    if (audio.paused) audio.play().catch(console.error);
    else audio.pause();
    return true;
  };

  // ===== Main API for pages =====
  window.phonyxSetTrack = function (opts) {
    if (!opts || !opts.src) return;

    // If queue exists and id is inside it, sync index
    const tid = (opts.trackId ?? opts.id ?? '');
    if (tid) setCurrentById(tid);

    audio.src = opts.src;
    audio.currentTime = 0;

    applyMeta(opts);

    if (opts.autoplay === false) audio.pause();
    else audio.play().catch(console.error);

    shell.classList.remove('collapsed');

    emit('phonyx:trackchange');
  };

  // ===== Default track on load =====
  const defSrc = shell.dataset.defaultSrc;
  const defTitle = shell.dataset.defaultTitle;
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
