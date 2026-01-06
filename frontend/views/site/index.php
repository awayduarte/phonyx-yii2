<?php
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'PHONYX';


$identity = Yii::$app->user->identity ?? null;
$displayName = $identity
    ? ($identity->username ?? $identity->email ?? 'User')
    : null;


$toggleLikeUrl = Url::to(['playlist/toggle-like']);
$csrf          = Yii::$app->request->csrfToken;


$this->registerJs(<<<JS
(function () {
  const audio        = document.getElementById('phonyx-audio');
  const playBtn      = document.getElementById('player-play');
  const playIcon     = document.getElementById('player-play-icon');
  const progressBar  = document.getElementById('player-progress-bar');
  const progressFill = document.getElementById('player-progress-fill');
  const currentTimeEl= document.getElementById('player-current-time');
  const durationEl   = document.getElementById('player-duration');
  const volumeSlider = document.getElementById('player-volume');
  const shell        = document.getElementById('player-shell');
  const toggleBtn    = document.getElementById('player-toggle');

  function formatTime(sec) {
    if (isNaN(sec)) return '0:00';
    const minutes = Math.floor(sec / 60);
    const seconds = Math.floor(sec % 60).toString().padStart(2,'0');
    return minutes + ':' + seconds;
  }

  if (audio) {
    audio.addEventListener('loadedmetadata', function() {
      if (durationEl) {
        durationEl.textContent = formatTime(audio.duration);
      }
    });

    audio.addEventListener('timeupdate', function() {
      if (currentTimeEl) {
        currentTimeEl.textContent = formatTime(audio.currentTime);
      }
      if (progressFill && audio.duration) {
        const percent = (audio.currentTime / audio.duration) * 100;
        progressFill.style.width = (percent || 0) + '%';
      }
    });
  }

  if (playBtn && audio && playIcon) {
    playBtn.addEventListener('click', function() {
      if (audio.paused) {
        audio.play();
        playIcon.textContent = '❚❚';
      } else {
        audio.pause();
        playIcon.textContent = '▶';
      }
    });
  }

  if (progressBar && audio && progressFill) {
    progressBar.addEventListener('click', function(e) {
      if (!audio.duration) return;
      const rect    = progressBar.getBoundingClientRect();
      const clickX  = e.clientX - rect.left;
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

 
  if (toggleBtn && shell) {
    toggleBtn.addEventListener('click', function() {
      shell.classList.toggle('collapsed');
      toggleBtn.textContent = shell.classList.contains('collapsed') ? '▴' : '▾';
    });
  }

  // ====== BOTÃO DE GOSTO NO PLAYER ======
  const likeBtn       = document.getElementById('player-like-btn');
  const csrfToken     = '$csrf';
  const toggleLikeUrl = '$toggleLikeUrl';

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

  if (likeBtn && shell) {
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
      .then(resp => resp.json())
      .then(data => {
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
      .catch(err => console.error(err));
    });
  }

})();
JS);
?>

<div class="phonyx-hero">
    <!-- HERO / CIRCLES -->
    <section class="hero-wrapper">

        <div class="bg-letters">
            <span>P</span>
            <span>X</span>
        </div>

        <div class="circle-container">
            <!-- Left Circle -->
            <div class="circle circle-left">
                <div class="circle-text">
                    <span class="arrow">↗</span>
                    <p>A plataforma que ajuda novos artistas a pôr a música no mapa.</p>
                </div>
            </div>

            <!-- Middle -->
            <div class="circle circle-center">
                <h1 class="track-title">Disstrack Albert</h1>
                <p class="track-subtitle">BY PHONYX ORIGINALS</p>
                <button class="btn-play">Play Track →</button>
            </div>

            <!-- Right Circle -->
            <div class="circle circle-right">
                <img src="<?= Yii::getAlias('@web/img/drake-circlee') ?>" alt="">
            </div>
        </div>

    </section>

    <!-- FEATURE CARDS -->
    <section class="feature-section">
        <div class="feature-card">
            <div class="feature-img img1"></div>
            <div>
                <h3>Cria o teu perfil único</h3>
                <p>Mostra quem és e começa a destacar-te na PHONYX.</p>
                <button class="small-btn">Explore</button>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-img img2"></div>
            <div>
                <h3>Feedback da comunidade</h3>
                <p>Recebe apoio real de ouvintes e artistas.</p>
                <div class="tag-row">
                    <span class="tag">Menu</span>
                    <span class="tag">Records</span>
                    <span class="tag">Progress +</span>
                </div>
            </div>
        </div>

        <div class="feature-card">
            <div class="feature-img img3"></div>
            <div>
                <h3>Payouts justos</h3>
                <p>Transparência total nos teus ganhos.</p>
                <button class="small-btn">Explore</button>
            </div>
        </div>
    </section>

</div> <!-- fecha .phonyx-hero -->

<!-- CONTINUAR A OUVIR / ÚLTIMAS FAIXAS -->
<section class="section continue-section">
    <div class="section-header">
        <h2>Continuar a ouvir</h2>
        <p class="section-subtitle">Retoma aquilo que deixaste em loop.</p>
    </div>

    <div class="continue-grid">
        <div class="continue-featured">
            <div class="continue-cover"></div>
            <div class="continue-meta">
                <span class="pill pill-now">Agora a tocar</span>
                <h3>Disstrack Albert</h3>
                <p class="artist">PHONYX ORIGINALS</p>
                <button class="small-btn ghost" type="button">
                    ▶ Continuar
                </button>
            </div>
        </div>

        <div class="continue-list">
            <div class="continue-item">
                <div class="continue-thumb"></div>
                <div class="continue-text">
                    <span class="track-name">Night Drive in Leiria</span>
                    <span class="track-artist">Duarte Maia</span>
                </div>
                <button class="icon-button" type="button">▶</button>
            </div>
            <div class="continue-item">
                <div class="continue-thumb"></div>
                <div class="continue-text">
                    <span class="track-name">Lo-Fi da Biblioteca</span>
                    <span class="track-artist">G. Antunes</span>
                </div>
                <button class="icon-button" type="button">▶</button>
            </div>
            <div class="continue-item">
                <div class="continue-thumb"></div>
                <div class="continue-text">
                    <span class="track-name">Cafezinho & Beats</span>
                    <span class="track-artist">H. Neves</span>
                </div>
                <button class="icon-button" type="button">▶</button>
            </div>
            <div class="continue-item">
                <div class="continue-thumb"></div>
                <div class="continue-text">
                    <span class="track-name">PHONYX Warmup</span>
                    <span class="track-artist">Various Artists</span>
                </div>
                <button class="icon-button" type="button">▶</button>
            </div>
        </div>
    </div>
</section>

<!-- DESCOBRE POR GÉNERO / MOOD -->
<section class="section mood-section">
    <div class="section-header">
        <h2>Descobre por género / mood</h2>
        <p class="section-subtitle">Escolhe o teu estado de espírito, nós tratamos do resto.</p>
    </div>

    <div class="mood-chips">
        <button class="mood-chip">Chill</button>
        <button class="mood-chip">Focus</button>
        <button class="mood-chip">Party</button>
        <button class="mood-chip">Lo-fi</button>
        <button class="mood-chip">Rap</button>
        <button class="mood-chip">Rock</button>
        <button class="mood-chip">Pop</button>
        <button class="mood-chip">Indie</button>
    </div>
</section>

<!-- TOP DO MOMENTO -->
<section class="section top-section">
    <div class="section-header">
        <h2>Top do momento</h2>
        <p class="section-subtitle">O que anda a bater na PHONYX.</p>
    </div>

    <div class="top-list">
        <div class="top-item">
            <span class="top-rank">1</span>
            <div class="top-cover"></div>
            <div class="top-meta">
                <span class="track-name">Disstrack Albert</span>
                <span class="track-artist">PHONYX ORIGINALS</span>
            </div>
            <button class="icon-button" type="button">▶</button>
        </div>
        <div class="top-item">
            <span class="top-rank">2</span>
            <div class="top-cover"></div>
            <div class="top-meta">
                <span class="track-name">Campus Nights</span>
                <span class="track-artist">PHONYX Collective</span>
            </div>
            <button class="icon-button" type="button">▶</button>
        </div>
        <div class="top-item">
            <span class="top-rank">3</span>
            <div class="top-cover"></div>
            <div class="top-meta">
                <span class="track-name">Late Lab Session</span>
                <span class="track-artist">Benavente</span>
            </div>
            <button class="icon-button" type="button">▶</button>
        </div>
        <div class="top-item">
            <span class="top-rank">4</span>
            <div class="top-cover"></div>
            <div class="top-meta">
                <span class="track-name">Coffee Break Beats</span>
                <span class="track-artist">Lo-fi ESTG</span>
            </div>
            <button class="icon-button" type="button">▶</button>
        </div>
        <div class="top-item">
            <span class="top-rank">5</span>
            <div class="top-cover"></div>
            <div class="top-meta">
                <span class="track-name">Sunset no Campus</span>
                <span class="track-artist">PHONYX Sessions</span>
            </div>
            <button class="icon-button" type="button">▶</button>
        </div>
    </div>
</section>

<!-- BLOCO ESPECIAL PARA ARTISTAS -->
<section class="section artist-cta">
    <div class="artist-cta-inner">
        <div class="artist-cta-text">
            <h2>És artista? Sobe a tua música à PHONYX.</h2>
            <p class="section-subtitle">
                A plataforma foi pensada para te pôr no mapa, não para te afogar no algoritmo.
            </p>
            <ul class="artist-bullets">
                <li>Faz upload das tuas faixas</li>
                <li>Acompanha estatísticas em tempo real</li>
                <li>Ganha com cada reprodução</li>
            </ul>
        </div>
        <div class="artist-cta-action">
            <a href="#" class="btn btn-accent btn-big">
                Começar como artista
            </a>
        </div>
    </div>
</section>

<!-- COMO FUNCIONA EM 3 PASSOS -->
<section class="section how-section">
    <div class="section-header">
        <h2>Como funciona</h2>
        <p class="section-subtitle">
            Em poucos cliques estás a ouvir e a partilhar música.
        </p>
    </div>

    <div class="how-grid">
        <div class="how-card">
            <div class="how-icon">1</div>
            <h3>Cria a tua conta</h3>
            <p>Regista-te em segundos e escolhe se és ouvinte, artista ou ambos.</p>
        </div>
        <div class="how-card">
            <div class="how-icon">2</div>
            <h3>Explora e segue artistas</h3>
            <p>Descobre novos sons, segue perfis e constrói a tua biblioteca.</p>
        </div>
        <div class="how-card">
            <div class="how-icon">3</div>
            <h3>Guarda e partilha playlists</h3>
            <p>Cria playlists para cada mood e partilha com a tua crew.</p>
        </div>
    </div>
</section>

<!-- PREVIEW DA APP MOBILE -->
<section class="section mobile-preview">
    <div class="mobile-inner">
        <div class="mobile-mockup">
            <div class="mobile-screen">
                <span class="pill pill-soon">Brevemente</span>
                <h3>PHONYX mobile</h3>
                <p>Leva a plataforma contigo, do campus ao comboio.</p>
            </div>
        </div>
        <div class="mobile-text">
            <h2>Leva a PHONYX contigo</h2>
            <ul class="mobile-bullets">
                <li>Sincroniza playlists entre web e Android</li>
                <li>Continua a ouvir onde paraste</li>
                <li>(No futuro) QR para download direto</li>
            </ul>
        </div>
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
