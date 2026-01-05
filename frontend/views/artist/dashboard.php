<?php
/** @var yii\web\View $this */
/** @var common\models\Artist $artist */
/** @var common\models\User $user */
/** @var common\models\Track[] $tracks */
/** @var common\models\Album[] $albums */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Painel de artista | PHONYX';

$totalTracks = is_array($tracks) ? count($tracks) : 0;
$totalAlbums = is_array($albums) ? count($albums) : 0;
$totalPlays = 0;
?>

<div class="artist-dash-wrapper">

    <div class="artist-dash-header">
        <div>
            <span class="artist-dash-chip">Painel de artista</span>
            <h1>Olá, <?= Html::encode($artist->stage_name ?: $user->username) ?></h1>
            <p>
                Este é o teu hub para gerir faixas, álbuns e acompanhar as tuas estatísticas
                na PHONYX.
            </p>
        </div>

        <div class="artist-dash-header-actions">
            <a href="<?= Url::to(['artist/edit']) ?>" class="artist-dash-pill">
                Edit artist profile
            </a>

            <!-- FIX: $model não existe -> usa $artist -->
            <a href="<?= Url::to(['artist/view', 'id' => $artist->id]) ?>"
                class="artist-edit-action-btn artist-edit-action-btn--accent">
                View public profile
            </a>

            <a href="<?= Url::to(['user/profile']) ?>" class="artist-dash-back">
                ← Back to profile
            </a>
        </div>
    </div>

    <div class="artist-dash-grid">

        <!-- CARD 1: QUICK ACTIONS -->
        <div class="artist-dash-card">
            <h2>Ações rápidas</h2>
            <p>Começa por aqui:</p>

            <div class="artist-dash-quick">
                <!-- FIX: link real -->
                <a href="<?= Url::to(['album/create']) ?>" class="artist-dash-pill">+ Criar álbum</a>
                <a href="#" class="artist-dash-pill ghost">Ver estatísticas</a>
            </div>

            <p class="artist-dash-note">
                (Mais tarde estes botões vão levar para os ecrãs de upload e gestão real.)
            </p>
        </div>

        <!-- CARD 2: OVERVIEW -->
        <div class="artist-dash-card">
            <h2>Visão geral</h2>

            <ul class="artist-dash-stats">
                <li><span class="label">Faixas</span> <span class="value"><?= (int) $totalTracks ?></span></li>
                <li><span class="label">Álbuns</span> <span class="value"><?= (int) $totalAlbums ?></span></li>
                <li><span class="label">Total de plays</span> <span class="value"><?= (int) $totalPlays ?></span></li>
            </ul>

            <p class="artist-dash-note">
                Assim que começares a subir música, os números vão aparecer aqui.
            </p>
        </div>

        <!-- CARD 3: PUBLIC PROFILE -->
        <div class="artist-dash-card">
            <h2>Perfil público</h2>

            <p class="artist-dash-bio">
                <?= $artist->bio
                    ? nl2br(Html::encode($artist->bio))
                    : "Ainda não escreveste uma biografia. Atualiza-a para os ouvintes saberem quem és e qual é a tua vibe." ?>
            </p>
        </div>

    </div>

    <!-- CARD 4: YOUR TRACKS -->
    <div class="artist-dash-card" style="margin-top:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div>
                <h2>As tuas faixas</h2>
                <p class="artist-dash-note" style="margin-top:6px;">
                    Manage your uploads: play, edit and delete.
                </p>
            </div>

            <a href="<?= Url::to(['track/create']) ?>" class="artist-dash-pill">
                + Enviar faixa
            </a>
        </div>

        <?php if (empty($tracks)): ?>
            <p class="artist-dash-note" style="margin-top:14px;">
                Ainda não tens faixas. Faz upload da primeira 🙂
            </p>
        <?php else: ?>
            <div class="artist-tracks-list" style="margin-top:14px;">
                <?php foreach ($tracks as $track): ?>
                    <?php
                    $coverUrl = Yii::getAlias('@web') . '/img/default-cover.png';
                    if (!empty($track->cover_path)) {
                        $coverUrl = Yii::getAlias('@web') . '/' . ltrim($track->cover_path, '/');
                    }

                    $audioUrl = null;
                    if (!empty($track->file_path)) {
                        $audioUrl = Yii::getAlias('@web') . '/' . ltrim($track->file_path, '/');
                    }

                    $viewUrl = Url::to(['track/view', 'id' => $track->id]);
                    ?>

                    <div class="artist-track-row"
                        style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-top:1px solid rgba(255,255,255,.06);">
                        <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                            <a href="<?= $viewUrl ?>"
                                style="display:block;width:44px;height:44px;border-radius:10px;overflow:hidden;flex:0 0 auto;">
                                <img src="<?= Html::encode($coverUrl) ?>" alt=""
                                    style="width:100%;height:100%;object-fit:cover;display:block;">
                            </a>

                            <div style="min-width:0;">
                                <a href="<?= $viewUrl ?>"
                                    style="color:#fff;text-decoration:none;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?= Html::encode($track->title ?: 'Sem título') ?>
                                </a>

                                <div style="opacity:.75;font-size:13px;">
                                    <?= Yii::$app->formatter->asDate($track->created_at) ?>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;gap:10px;flex:0 0 auto;">
                            <?php if ($audioUrl): ?>
                                <button type="button" class="dash-play-btn" data-id="<?= (int) $track->id ?>"
                                    data-audio="<?= Html::encode($audioUrl) ?>"
                                    data-title="<?= Html::encode($track->title ?? '') ?>"
                                    data-artist="<?= Html::encode($artist->stage_name ?: $user->username) ?>"
                                    data-cover="<?= Html::encode($coverUrl) ?>" aria-label="Play"
                                    style="width:42px;height:42px;border-radius:999px;border:1px solid rgba(255,255,255,.18);background:transparent;color:#fff;">▶</button>
                            <?php else: ?>
                                <span style="opacity:.6;font-size:13px;">Sem áudio</span>
                            <?php endif; ?>

                            <a href="<?= Url::to(['track/update', 'id' => $track->id]) ?>" class="btn btn-ghost"
                                style="padding:8px 12px">Edit</a>

                            <?= Html::beginForm(['artist/delete-track', 'id' => $track->id], 'post', ['style' => 'display:inline;']) ?>
                            <?= Html::submitButton('Delete', [
                                'class' => 'artist-dash-pill ghost',
                                'style' => 'padding:8px 12px;border-color:rgba(255,90,90,.4);',
                                'onclick' => "return confirm('Are you sure you want to delete this track?');",
                            ]) ?>
                            <?= Html::endForm() ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- CARD 5: YOUR ALBUMS -->
    <div class="artist-dash-card" style="margin-top:18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div>
                <h2>Os teus álbuns</h2>
                <p class="artist-dash-note" style="margin-top:6px;">
                    Manage your albums: edit and delete.
                </p>
            </div>

            <a href="<?= Url::to(['album/create']) ?>" class="artist-dash-pill">
                + Criar álbum
            </a>
        </div>

        <?php if (empty($albums)): ?>
            <p class="artist-dash-note" style="margin-top:14px;">
                Ainda não tens álbuns. Cria o primeiro 🙂
            </p>
        <?php else: ?>
            <div class="artist-tracks-list" style="margin-top:14px;">
                <?php foreach ($albums as $album): ?>
                    <?php
                    $albumCoverUrl = Yii::getAlias('@web') . '/img/default-cover.png';
                    if (!empty($album->cover_path)) {
                        $albumCoverUrl = Yii::getAlias('@web') . '/' . ltrim($album->cover_path, '/');
                    }

                    // se não tiveres album/view, podes apontar para update
                    $albumViewUrl = Url::to(['album/update', 'id' => $album->id]);

                    // contador de faixas do álbum (assume track.album_id existe)
                    $albumTracksCount = (int) \common\models\Track::find()
                        ->where(['album_id' => $album->id])
                        ->count();
                    ?>

                    <div class="artist-track-row"
                        style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-top:1px solid rgba(255,255,255,.06);">
                        <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                            <a href="<?= $albumViewUrl ?>"
                                style="display:block;width:44px;height:44px;border-radius:10px;overflow:hidden;flex:0 0 auto;">
                                <img src="<?= Html::encode($albumCoverUrl) ?>" alt=""
                                    style="width:100%;height:100%;object-fit:cover;display:block;">
                            </a>

                            <div style="min-width:0;">
                                <a href="<?= $albumViewUrl ?>"
                                    style="color:#fff;text-decoration:none;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?= Html::encode($album->title ?: 'Sem título') ?>
                                </a>

                                <div style="opacity:.75;font-size:13px;display:flex;gap:10px;flex-wrap:wrap;">
                                    <span><?= Yii::$app->formatter->asDate($album->created_at) ?></span>
                                    <span>•</span>
                                    <span><?= $albumTracksCount ?> faixas</span>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;gap:10px;flex:0 0 auto;">
                            <a href="<?= Url::to(['album/update', 'id' => $album->id]) ?>" class="btn btn-ghost"
                                style="padding:8px 12px">Edit</a>

                            <form action="<?= Url::to(['/album/delete', 'id' => $album->id]) ?>" method="post"
                                style="display:inline;">
                                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>"
                                    value="<?= Yii::$app->request->csrfToken ?>">
                                <button type="submit" class="artist-dash-pill ghost"
                                    style="padding:8px 12px;border-color:rgba(255,90,90,.4);"
                                    onclick="return confirm('Are you sure you want to delete this album?');">
                                    Delete
                                </button>
                            </form>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php
$this->registerJs(<<<JS
(function(){
  let currentBtn = null;

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.dash-play-btn');
    if (!btn) return;

    if (typeof window.phonyxSetTrack !== 'function') return;

    const src = btn.dataset.audio;
    if (!src) return;

    if (currentBtn === btn && typeof window.phonyxTogglePlay === 'function') {
      window.phonyxTogglePlay();
      return;
    }

    if (currentBtn && currentBtn !== btn) currentBtn.textContent = '▶';

    currentBtn = btn;
    btn.textContent = '⏸';

    window.phonyxSetTrack({
      id: btn.dataset.id || '',
      src: src,
      title: btn.dataset.title || '',
      artist: btn.dataset.artist || '',
      cover: btn.dataset.cover || ''
    });
  });
})();
JS);
?>