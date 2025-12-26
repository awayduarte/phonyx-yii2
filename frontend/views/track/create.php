<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Enviar faixa | PHONYX';
?>

<div class="artist-auth-page">
    <div class="artist-auth-card">

        <h1 class="artist-auth-title">Enviar nova faixa</h1>
        <p class="artist-auth-subtitle">
            Define o título, género, feat. e faz upload do ficheiro de áudio.
        </p>

        <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
            'fieldConfig' => [
                'template' =>
                    "<div class=\"artist-auth-field\">
                        {label}
                        {input}
                        <div class=\"artist-auth-error\">{error}</div>
                    </div>",
                'labelOptions' => ['class' => 'artist-auth-label'],
                'inputOptions' => ['class' => 'artist-auth-input form-control'],
            ],
        ]); ?>

            <?= $form->field($model, 'title')
                ->textInput(['maxlength' => true, 'placeholder' => 'Título da faixa']); ?>

            <?= $form->field($model, 'genre_id')
                ->dropDownList($genreOptions, [
                    'prompt' => 'Selecionar género',
                    'class' => 'artist-auth-input',
                ]); ?>

            <div class="artist-auth-field">
                <label class="artist-auth-label" for="feat-search-input">
                    Feat. com
                </label>

                <input
                    type="text"
                    id="feat-search-input"
                    class="artist-auth-input"
                    placeholder="Procurar artista pelo nome"
                    autocomplete="off"
                >

                <div class="feat-search-results" id="feat-search-results">
                    <?php if (!empty($artistOptions)): ?>
                        <?php foreach ($artistOptions as $id => $name): ?>
                            <button
                                type="button"
                                class="feat-search-item"
                                data-id="<?= (int)$id ?>"
                                data-name="<?= Html::encode($name) ?>"
                            >
                                <?= Html::encode($name) ?>
                            </button>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="feat-search-empty">
                            Ainda não existem outros artistas registados.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="feat-selected" id="feat-selected"></div>
                <div class="artist-auth-error"></div>
            </div>

            <?= $form->field($model, 'audioFile')
                ->fileInput(['class' => 'artist-auth-input']); ?>

            <?= $form->field($model, 'coverFile')
                ->fileInput(['class' => 'artist-auth-input']); ?>

            <div class="artist-auth-actions">
                <?= Html::submitButton('Enviar faixa', [
                    'class' => 'artist-auth-submit',
                ]) ?>

                <a href="<?= \yii\helpers\Url::to(['artist/dashboard']) ?>"
                   class="artist-auth-cancel">
                    Cancelar
                </a>
            </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<?php
$this->registerJs(<<<JS
(function() {
    var input = document.getElementById('feat-search-input');
    var results = document.getElementById('feat-search-results');
    var selectedWrap = document.getElementById('feat-selected');
    if (!input || !results || !selectedWrap) return;

    var items = Array.prototype.slice.call(results.querySelectorAll('.feat-search-item'));

    input.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        if (!q) {
            results.style.display = 'none';
            items.forEach(function(btn) {
                btn.style.display = 'none';
            });
            return;
        }

        var any = false;
        items.forEach(function(btn) {
            var name = (btn.getAttribute('data-name') || '').toLowerCase();
            if (name.indexOf(q) !== -1) {
                btn.style.display = 'block';
                any = true;
            } else {
                btn.style.display = 'none';
            }
        });

        results.style.display = any ? 'block' : 'none';
    });

    results.addEventListener('click', function(e) {
        var btn = e.target.closest('.feat-search-item');
        if (!btn) return;

        var id = btn.getAttribute('data-id');
        var name = btn.getAttribute('data-name');

        if (selectedWrap.querySelector('.feat-selected-pill[data-id="' + id + '"]')) {
            input.value = '';
            results.style.display = 'none';
            return;
        }

        var pill = document.createElement('div');
        pill.className = 'feat-selected-pill';
        pill.setAttribute('data-id', id);
        pill.textContent = name;

        var remove = document.createElement('span');
        remove.className = 'feat-selected-remove';
        remove.textContent = '×';
        pill.appendChild(remove);

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'Track[featuredArtistIds][]';
        hidden.value = id;
        pill.appendChild(hidden);

        selectedWrap.appendChild(pill);

        input.value = '';
        results.style.display = 'none';
    });

    selectedWrap.addEventListener('click', function(e) {
        if (!e.target.classList.contains('feat-selected-remove')) return;
        var pill = e.target.closest('.feat-selected-pill');
        if (pill) pill.remove();
    });

    document.addEventListener('click', function(e) {
        if (e.target === input || results.contains(e.target)) return;
        results.style.display = 'none';
    });
})();
JS);
?>
