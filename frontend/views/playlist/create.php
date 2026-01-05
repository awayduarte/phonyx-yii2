<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Playlist $model */
/** @var common\models\Track[] $availableTracks */

$this->title = 'Criar playlist';
?>

<div class="playlist-create" style="max-width:900px;margin:0 auto;padding:22px 16px;">

    <h1 style="margin:0 0 14px 0; font-size:34px; font-weight:800; color:#fff;">
        Criar playlist
    </h1>


    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div
            style="padding:12px;border-radius:12px;border:1px solid rgba(255,80,80,.4);background:rgba(255,80,80,.12);color:#fff;margin-bottom:12px;">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div
            style="padding:12px;border-radius:12px;border:1px solid rgba(80,255,160,.35);background:rgba(80,255,160,.10);color:#fff;margin-bottom:12px;">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?= \yii\helpers\Html::errorSummary($model, [
        'style' => 'padding:12px;border-radius:12px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.25);color:#fff;margin-bottom:12px;'
    ]) ?>


    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:start;">
        <div
            style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:14px;">
            <label style="display:block;color:#fff;font-weight:700;margin-bottom:8px;">Título</label>
            <?= $form->field($model, 'title')->textInput([
                'placeholder' => 'Nome da playlist…',
                'style' => 'width:100%;padding:12px 12px;border-radius:12px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.25);color:#fff;'
            ])->label(false); ?>

            <div style="height:10px"></div>

            <label style="display:block;color:#fff;font-weight:700;margin-bottom:8px;">Privacidade</label>
            <?php
            // tenta vários nomes (porque não sabemos o teu schema exato)
            $privacyAttr = null;
            foreach (['is_public', 'public', 'visibility', 'isPrivate'] as $a) {
                if ($model->hasAttribute($a)) {
                    $privacyAttr = $a;
                    break;
                }
            }
            ?>

            <?php if ($privacyAttr): ?>
                <?= $form->field($model, $privacyAttr)->dropDownList(
                    [1 => 'Pública', 0 => 'Privada'],
                    ['style' => 'width:100%;padding:12px;border-radius:12px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.25);color:#fff;']
                )->label(false); ?>
            <?php else: ?>
                <div style="opacity:.75;color:#fff;font-size:13px;">
                    (Sem coluna de privacidade no modelo — a playlist ficará com a configuração default do teu DB.)
                </div>
            <?php endif; ?>
        </div>

        <div
            style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:14px;">
            <label style="display:block;color:#fff;font-weight:700;margin-bottom:8px;">Capa (opcional)</label>

            <?php

            ?>
            <input type="file" name="cover_file"
                style="width:100%;padding:12px;border-radius:12px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.25);color:#fff;">

            <div style="margin-top:10px;opacity:.7;color:#fff;font-size:13px;">
                Se o teu projeto já tem upload de capa, liga este input ao teu handler de upload (Asset).
            </div>
        </div>
    </div>

    <div style="height:18px"></div>

    <!-- TRACKS -->
    <div
        style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div style="color:#fff;font-weight:800;font-size:16px;">
                Faixas na playlist <span style="opacity:.7;font-weight:600;">(máx. 20)</span>
            </div>
            <div id="countBadge" style="color:#fff;opacity:.8;">0/20</div>
        </div>

        <div style="height:10px"></div>

        <input id="trackSearch" type="text" placeholder="Pesquisar faixas…"
            style="width:100%;padding:12px 12px;border-radius:12px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.25);color:#fff;">

        <div style="height:12px"></div>

        <div id="trackList" style="display:grid;gap:10px;max-height:320px;overflow:auto;padding-right:6px;">
            <?php if (empty($availableTracks)): ?>
                <div
                    style="opacity:.75;color:#fff;padding:10px;border:1px dashed rgba(255,255,255,.12);border-radius:12px;">
                    Ainda não tens faixas disponíveis para adicionar.
                </div>
            <?php else: ?>
                <?php foreach ($availableTracks as $t): ?>
                    <?php
                    $artistName = 'Unknown artist';
                    if ($t->artist)
                        $artistName = $t->artist->stage_name ?? 'Unknown artist';
                    ?>
                    <div class="track-row" data-id="<?= (int) $t->id ?>" data-title="<?= Html::encode($t->title ?? '') ?>"
                        data-artist="<?= Html::encode($artistName) ?>"
                        style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.18);">
                        <div style="min-width:0;">
                            <div style="color:#fff;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= Html::encode($t->title ?? 'Untitled') ?>
                            </div>
                            <div style="color:#fff;opacity:.7;font-size:13px;">
                                <?= Html::encode($artistName) ?>
                            </div>
                        </div>
                        <button type="button" class="addTrackBtn"
                            style="padding:10px 14px;border-radius:999px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:#fff;cursor:pointer;">
                            + Adicionar
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="height:12px"></div>

        <div style="color:#fff;font-weight:800;">Selecionadas</div>
        <div id="selectedWrap" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;"></div>

        <div id="trackIdsInputs"></div>
    </div>

    <div style="height:18px"></div>

    <div style="display:flex;gap:10px;align-items:center;">
        <button type="submit"
            style="padding:12px 16px;border-radius:999px;border:0;background:#fff;color:#000;font-weight:900;cursor:pointer;">
            Criar playlist
        </button>

        <a href="<?= Url::to(['playlist/index']) ?>"
            style="padding:12px 16px;border-radius:999px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:#fff;text-decoration:none;">
            Cancelar
        </a>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
    (function () {
        const MAX = 20;
        const selected = new Map();

        const countBadge = document.getElementById('countBadge');
        const selectedWrap = document.getElementById('selectedWrap');
        const inputsWrap = document.getElementById('trackIdsInputs');
        const search = document.getElementById('trackSearch');
        const list = document.getElementById('trackList');

        function renderSelected() {
            selectedWrap.innerHTML = '';
            inputsWrap.innerHTML = '';

            const ids = Array.from(selected.keys());

            ids.forEach(id => {
                const meta = selected.get(id);

                const chip = document.createElement('button');
                chip.type = 'button';
                chip.textContent = '✓ ' + meta.title + ' — ' + meta.artist + '  ×';
                chip.style.cssText = 'cursor:pointer;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:#fff;border-radius:999px;padding:10px 12px;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
                chip.addEventListener('click', () => {
                    selected.delete(id);
                    renderSelected();
                });
                selectedWrap.appendChild(chip);

                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'track_ids[]';
                inp.value = id;
                inputsWrap.appendChild(inp);
            });

            countBadge.textContent = ids.length + '/' + MAX;
        }

        list.addEventListener('click', function (e) {
            const btn = e.target.closest('.addTrackBtn');
            if (!btn) return;

            const row = btn.closest('.track-row');
            if (!row) return;

            const id = row.getAttribute('data-id');
            if (!id) return;

            if (selected.has(id)) return;

            if (selected.size >= MAX) {
                alert('Máximo de ' + MAX + ' faixas.');
                return;
            }

            selected.set(id, {
                title: row.getAttribute('data-title') || '',
                artist: row.getAttribute('data-artist') || ''
            });

            renderSelected();
        });

        search.addEventListener('input', function () {
            const q = (search.value || '').toLowerCase().trim();
            const rows = list.querySelectorAll('.track-row');
            rows.forEach(r => {
                const t = (r.getAttribute('data-title') || '').toLowerCase();
                const a = (r.getAttribute('data-artist') || '').toLowerCase();
                const show = !q || t.includes(q) || a.includes(q);
                r.style.display = show ? '' : 'none';
            });
        });

        renderSelected();
    })();
</script>