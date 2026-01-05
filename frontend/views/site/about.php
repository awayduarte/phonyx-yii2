<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'About';


$this->registerJs(<<<JS
const audio = document.getElementById('phonyx-audio');
const playBtn = document.getElementById('player-play');
const playIcon = document.getElementById('player-play-icon');
const progressBar = document.getElementById('player-progress-bar');
const progressFill = document.getElementById('player-progress-fill');
const currentTimeEl = document.getElementById('player-current-time');
const durationEl = document.getElementById('player-duration');
const volumeSlider = document.getElementById('player-volume');
const shell = document.getElementById('player-shell');
const toggleBtn = document.getElementById('player-toggle');

function formatTime(sec) {
  if (isNaN(sec)) return '0:00';
  const minutes = Math.floor(sec / 60);
  const seconds = Math.floor(sec % 60).toString().padStart(2,'0');
  return minutes + ':' + seconds;
}

if (audio) {
  audio.addEventListener('loadedmetadata', function() {
    durationEl.textContent = formatTime(audio.duration);
  });

  audio.addEventListener('timeupdate', function() {
    currentTimeEl.textContent = formatTime(audio.currentTime);
    const percent = (audio.currentTime / audio.duration) * 100;
    progressFill.style.width = (percent || 0) + '%';
  });
}

if (playBtn) {
  playBtn.addEventListener('click', function() {
    if (!audio) return;
    if (audio.paused) {
      audio.play();
      playIcon.textContent = '❚❚';
    } else {
      audio.pause();
      playIcon.textContent = '▶';
    }
  });
}

if (progressBar) {
  progressBar.addEventListener('click', function(e) {
    if (!audio || !audio.duration) return;
    const rect = progressBar.getBoundingClientRect();
    const clickX = e.clientX - rect.left;
    const percent = clickX / rect.width;
    audio.currentTime = audio.duration * percent;
  });
}

if (volumeSlider && audio) {
  volumeSlider.addEventListener('input', function() {
    audio.volume = this.value;
  });
  audio.volume = volumeSlider.value;
}

// Minimizar / expandir player
if (toggleBtn && shell) {
  toggleBtn.addEventListener('click', function() {
    shell.classList.toggle('collapsed');
    if (shell.classList.contains('collapsed')) {
      toggleBtn.textContent = '▴'; 
    } else {
      toggleBtn.textContent = '▾'; 
    }
  });
}
JS);
?>

<div class="phonyx-hero">

    <section class="player-shell" id="player-shell">
        <div class="player-bar">
            <div class="player-left">
                <div class="player-cover"></div>
                <div class="player-meta">
                    <div class="player-title">Disstrack Albert</div>
                    <div class="player-artist">Phonyx • Single</div>
                </div>
            </div>

            <div class="player-center">
                <div class="player-controls">
                    <button class="player-btn player-small" type="button">⏮</button>
                    <button class="player-btn player-main" type="button" id="player-play">
                        <span id="player-play-icon">▶</span>
                    </button>
                    <button class="player-btn player-small" type="button">⏭</button>
                </div>
                <div class="player-timeline">
                    <span class="player-time" id="player-current-time">0:00</span>
                    <div class="player-progress" id="player-progress-bar">
                        <div class="player-progress-fill" id="player-progress-fill"></div>
                    </div>
                    <span class="player-time" id="player-duration">0:00</span>
                </div>
            </div>

            <div class="player-right">
                <span class="player-volume-icon">🔊</span>
                <input type="range" min="0" max="1" step="0.01" value="0.7" id="player-volume">
            </div>

            <button class="player-toggle" type="button" id="player-toggle">▾</button>
        </div>
        <audio
                id="phonyx-audio"
                src="<?= Yii::getAlias('@web/media/disstrack-albert.mp3') ?>">
        </audio>
    </section>

    <section class="about" id="about">
        <div class="site-about">
            <h1><?= Html::encode($this->title) ?></h1>

            <p>This is the About page. You may modify the following file to customize its content:</p>

            <code><?= __FILE__ ?></code>
        </div>
    </section>

    <!-- FOOTER TECH VIBES -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-left">
                <span class="footer-logo">PHONYX</span>
                <span class="footer-tagline">Streaming para a nova geração de artistas.</span>
            </div>
            <div class="footer-links">
                <a href="#" class="footer-link">Sobre</a>
                <a href="#" class="footer-link">Termos</a>
                <a href="#" class="footer-link">Contacto</a>
            </div>
            <div class="footer-tech">
                <span>Powered by</span>
                <span class="footer-tech-stack">
                Yii2 • Codeception • PHONYX SI 2025/26
            </span>
            </div>
        </div>
    </footer>