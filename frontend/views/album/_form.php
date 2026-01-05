<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var array $trackOptions */
/** @var array $selectedTrackIds */

$trackOptions = $trackOptions ?? [];
$selectedTrackIds = array_map('intval', $selectedTrackIds ?? []);

$form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data']
]);

// cover url (usa Asset)
$coverUrl = Yii::getAlias('@web') . '/img/default-cover.png';
if (!empty($model->coverAsset) && !empty($model->coverAsset->path)) {
    $p = (string)$model->coverAsset->path;

    // se já for URL absoluta
    if (preg_match('~^https?://~i', $p)) {
        $coverUrl = $p;
    } else {
        $coverUrl = Yii::getAlias('@web') . '/' . ltrim($p, '/');
    }
}
?>

<div class="artist-dash-card album-form" style="max-width:720px;margin:0 auto;">
    <h2 class="album-form-title"><?= Html::encode($this->title) ?></h2>
    <p class="artist-dash-note album-form-subtitle">
        Define o título, capa e as faixas do álbum (máx. 20).
    </p>

    <?= $form->field($model, 'title')
        ->textInput(['maxlength' => true])
        ->label('Title') ?>

    <?php if (property_exists($model, 'coverFile')): ?>
        <div class="album-cover-block">
            <label class="album-cover-label">Album cover</label>

            <div class="album-cover-row">
                <div class="album-cover-preview">
                    <img src="<?= Html::encode($coverUrl) ?>" alt="Album cover" />
                </div>

                <div class="album-cover-actions">
                    <?= $form->field($model, 'coverFile')
                        ->fileInput()
                        ->label(false) ?>

                    <?php if (!empty($model->cover_asset_id)): ?>
                        <label class="album-remove-cover">
                            <input type="checkbox" name="removeCover" value="1">
                            Remover capa atual
                        </label>

                        <div class="album-cover-hint">
                            Se enviares uma nova capa, ela substitui a atual automaticamente.
                        </div>
                    <?php else: ?>
                        <div class="album-cover-hint">
                            Opcional: adiciona uma imagem (png/jpg/webp).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- TRACK SELECTION -->
    <div class="album-track-section">
        <label class="album-track-label">
            Faixas no álbum <span class="album-track-limit">(máx. 20)</span>
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
                                <?= Html::encode($title ?: 'Sem título') ?>
                            </span>
                            <span class="album-track-meta">ID: <?= $id ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="album-track-footer">
                <p id="album-track-counter" class="album-track-counter">
                    Selecionadas: <strong><?= count($selectedTrackIds) ?></strong> / 20
                </p>

                <button type="button" class="artist-dash-pill ghost"
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

    <div class="album-form-footer">
        <?= Html::submitButton('Save', ['class' => 'artist-dash-pill']) ?>
        <?= Html::a('Cancel', ['artist/dashboard'], ['class' => 'artist-dash-back']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
(function(){
    const max = 20;
    const counter = document.getElementById('album-track-counter');

    function updateCounter() {
        const checked = document.querySelectorAll('.album-track-checkbox:checked').length;
        if (counter) {
            counter.innerHTML = 'Selecionadas: <strong>' + checked + '</strong> / ' + max;
        }
        return checked;
    }

    document.addEventListener('change', function(e){
        const cb = e.target.closest('.album-track-checkbox');
        if (!cb) return;

        const total = updateCounter();
        if (total > max) {
            cb.checked = false;
            updateCounter();
            alert('Um álbum pode ter no máximo ' + max + ' faixas.');
        }
    });

    updateCounter();
})();
JS);
?>


