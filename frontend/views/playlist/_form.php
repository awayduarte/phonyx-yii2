<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var common\models\Playlist $model */
?>

<div class="album-track-section">
    <label class="album-track-label">
        Faixas no álbum <span style="opacity:.75;font-weight:500;">(máx. 20)</span>
    </label>

    <?php if (!empty($trackOptions)): ?>
        <div class="album-track-box" id="album-track-box">
            <?php foreach ($trackOptions as $id => $title): ?>
                <?php $id = (int)$id; ?>
                <label class="album-track-item">
                    <input
                        type="checkbox"
                        class="album-track-checkbox"
                        name="trackIds[]"
                        value="<?= $id ?>"
                        <?= in_array($id, $selectedTrackIds, true) ? 'checked' : '' ?>
                    >

                    <span class="album-track-text">
                        <span class="album-track-title">
                            <?= \yii\helpers\Html::encode($title ?: 'Sem título') ?>
                        </span>
                        <span class="album-track-meta">
                            ID: <?= $id ?>
                        </span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="album-track-footer">
            <p id="album-track-counter" class="album-track-counter">
                Selecionadas: <strong><?= count($selectedTrackIds) ?></strong> / 20
            </p>

            <button type="button"
                class="artist-dash-pill ghost"
                onclick="
                    document.querySelectorAll('.album-track-checkbox').forEach(cb=>cb.checked=false);
                    const ev = new Event('change');
                    document.querySelectorAll('.album-track-checkbox').forEach(cb=>cb.dispatchEvent(ev));
                "
            >
                Limpar
            </button>
        </div>
    <?php else: ?>
        <div class="album-track-box">
            Ainda não tens faixas para adicionar a um álbum.
        </div>
    <?php endif; ?>
</div>
