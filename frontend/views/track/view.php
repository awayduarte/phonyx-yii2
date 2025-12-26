<?php
/** @var yii\web\View $this */
/** @var common\models\Track $model */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $model->title . ' | PHONYX';

// artistas em feat.
$featNames = [];
foreach ($model->featuredArtists as $featArtist) {
    $featNames[] = Html::encode($featArtist->artist_name);
}
$featLabel = $featNames ? ' feat. ' . implode(', ', $featNames) : '';
?>

<div class="track-page">

    <div class="track-hero">
        <div class="track-cover">
            <img src="<?= Html::encode($model->coverUrl) ?>"
                 alt="Capa da faixa <?= Html::encode($model->title) ?>">
        </div>

        <div class="track-main-info">
            <span class="track-label">Faixa</span>

            <h1 class="track-title-big">
                <?= Html::encode($model->title) ?>
            </h1>

            <div class="track-artist-line">
                <span class="track-artist">
                    <?= Html::encode($model->artistLabel) ?>
                </span>
                <?php if ($featLabel): ?>
                    <span class="track-feat"><?= $featLabel ?></span>
                <?php endif; ?>
            </div>

            <div class="track-meta-line">
                <?php if ($model->album): ?>
                    <span class="track-meta">Álbum:
                        <a href="<?= Url::to(['album/view', 'id' => $model->album->id]) ?>">
                            <?= Html::encode($model->album->title) ?>
                        </a>
                    </span>
                    <span class="track-dot">•</span>
                <?php endif; ?>

                <?php if ($model->genre): ?>
                    <span class="track-meta">
                        <?= Html::encode($model->genre->name) ?>
                    </span>
                    <span class="track-dot">•</span>
                <?php endif; ?>

                <span class="track-meta">
                    Duração <?= Html::encode($model->durationLabel) ?>
                </span>
            </div>

            <div class="track-actions">
                <?php if ($model->audioUrl): ?>
                    <audio controls class="track-audio">
                        <source src="<?= Html::encode($model->audioUrl) ?>" type="audio/mpeg">
                        O teu browser não suporta áudio HTML5.
                    </audio>
                <?php else: ?>
                    <p class="track-warning">
                        Ainda não há ficheiro de áudio associado a esta faixa.
                    </p>
                <?php endif; ?>

                <div class="track-buttons-row">
                    <button type="button" class="btn btn-accent track-play-main">
                        ▶ Tocar
                    </button>

                    <button type="button"
                            class="btn btn-ghost track-like-btn"
                            data-id="<?= (int)$model->id ?>">
                        ❤️ Gostar (<?= $model->likesCount ?>)
                    </button>

                    <button type="button"
                            class="btn btn-ghost track-add-playlist">
                        + Adicionar à playlist
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- bloco estatísticas / info extra -->
    <section class="track-extra">
        <div class="track-extra-card">
            <h2>Sobre esta faixa</h2>
            <p class="track-extra-text">
                Aqui depois posso escrever uma descrição mais completa da faixa:
                mood, contexto, etc. Por enquanto fica só como placeholder.
            </p>

            <ul class="track-extra-list">
                <li>Lançada em <?= Yii::$app->formatter->asDate($model->created_at) ?></li>
                <li><?= $model->likesCount ?> gostos na PHONYX</li>
            </ul>
        </div>

        <?php if ($model->artist && $model->artist->tracks): ?>
            <div class="track-extra-card">
                <h2>Mais de <?= Html::encode($model->artistLabel) ?></h2>

                <div class="track-more-list">
                    <?php foreach ($model->artist->tracks as $other): ?>
                        <?php if ($other->id === $model->id) continue; ?>
                        <a href="<?= Url::to(['track/view', 'id' => $other->id]) ?>"
                           class="track-more-item">
                            <div class="track-more-cover">
                                <img src="<?= Html::encode($other->coverUrl) ?>"
                                     alt="<?= Html::encode($other->title) ?>">
                            </div>
                            <div class="track-more-text">
                                <span class="track-more-title">
                                    <?= Html::encode($other->title) ?>
                                </span>
                                <span class="track-more-meta">
                                    <?= Html::encode($model->artistLabel) ?> • <?= Html::encode($other->durationLabel) ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

</div>
