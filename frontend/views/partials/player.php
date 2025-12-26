<?php
use yii\helpers\Html;
use yii\helpers\Url;

// rota para guardar “gosto”
$toggleLikeUrl = Url::to(['playlist/toggle-like']);
$csrf          = Yii::$app->request->csrfToken;

// faixa default (home)
$defaultSrc    = Yii::getAlias('@web/media/disstrack-albert.mp3');
$defaultTitle  = 'Disstrack Albert';
$defaultArtist = 'Phonyx · Single';
?>

<section class="player-shell" id="player-shell" data-track-id=""
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
                    title="Adicionar aos gostos">
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
  const audio        = document.getElementById('phonyx-audio');
  const shell        = document.getElementById('player-shell');
  if (!audio || !shell) return;

  const playBtn      = document.getElementById('player-play');
  const playIcon     = document.getElementById('player-play-icon');
  const progressBar  = document.getElementById('player-progress-bar');
  const progressFill = document.getElementById('player-progress-fill');
  const currentTimeEl= document.getElementById('player-current-time');
  const durationEl   = document.getElementById('player-duration');
  const volumeSlider = document.getElementById('player-volume');
  const toggleBtn    = document.getElementById('player-toggle');
  const likeBtn      = document.getElementById('player-like-btn');

  const titleEl      = shell.querySelector('.player-title');
  const artistEl     = shell.querySelector('.player-artist');
  const coverEl      = shell.querySelector('.player-cover');

  const csrfToken    = '$csrf';
  const toggleLikeUrl= '$toggleLikeUrl';

  function formatTime(sec) {
    if (!sec || isNaN(sec)) return '0:00';
    const minutes = Math.floor(sec / 60);
    const seconds = Math.floor(sec % 60).toString().padStart(2,'0');
    return minutes + ':' + seconds;
  }

  function updatePlayIcon() {
    if (!playIcon) return;
    playIcon.textContent = audio.paused ? '▶' : '❚❚';
  }

  // ====== EVENTOS DO AUDIO ======
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

  audio.addEventListener('play', updatePlayIcon);
  audio.addEventListener('pause', updatePlayIcon);

  // ====== CONTROLOS ======
  if (playBtn) {
    playBtn.addEventListener('click', function () {
      if (!audio.src) return;
      if (audio.paused) {
        audio.play().catch(console.error);
      } else {
        audio.pause();
      }
    });
  }

  if (progressBar) {
    progressBar.addEventListener('click', function (e) {
      if (!audio.duration) return;
      const rect    = progressBar.getBoundingClientRect();
      const clickX  = e.clientX - rect.left;
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

  // ====== GOSTOS ======
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

  if (likeBtn) {
    likeBtn.addEventListener('click', function () {
      const trackId = shell.dataset.trackId;
      if (!trackId) {
        console.warn('Nenhuma faixa ativa no player.');
        return;
      }

      fetch(toggleLikeUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: 'track_id=' + encodeURIComponent(trackId) + '&_csrf=' + encodeURIComponent(csrfToken)
      })
      .then(function (resp) { return resp.json(); })
      .then(function (data) {
        if (!data.success) {
          console.error(data.message || 'Erro ao guardar gosto.');
          return;
        }
        if (data.state === 'added') {
          setLikeState(true);
        } else if (data.state === 'removed') {
          setLikeState(false);
        }
      })
      .catch(console.error);
    });
  }

  // ====== FUNÇÃO GLOBAL PARA OUTRAS PÁGINAS ======
  window.phonyxSetTrack = function (opts) {
    if (!opts || !opts.src) return;

    audio.src = opts.src;
    audio.currentTime = 0;

    // autoplay por default, só não toca se opts.autoplay === false
    if (opts.autoplay === false) {
      audio.pause();
    } else {
      audio.play().catch(console.error);
    }

    if (titleEl && opts.title) {
      titleEl.textContent = opts.title;
    }
    if (artistEl && opts.artist) {
      artistEl.textContent = opts.artist;
    }
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

    // aceita tanto opts.trackId como opts.id
    var tid = (typeof opts.trackId !== 'undefined' && opts.trackId !== null && opts.trackId !== '')
              ? opts.trackId
              : (opts.id || '');

    shell.dataset.trackId = tid;

    setLikeState(!!opts.isLiked);
    shell.classList.remove('collapsed');
  };

  // ====== FAIXA DEFAULT AO CARREGAR A PÁGINA ======
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
      autoplay: false    // não começa logo a tocar
    });
  }
})();
JS);
?>
