<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Playlist $playlist */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var common\models\Track[] $availableTracks */

$this->title = 'Tracks – ' . $playlist->title;
$this->params['breadcrumbs'][] = ['label' => 'Playlists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="playlist-tracks">

    <p>
        <?= Html::a('← Back to playlists', ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <!-- ========================= -->
    <!-- Add track to playlist -->
    <!-- ========================= -->
    <div class="card mb-4">
        <div class="card-header">
            <strong>Add track to playlist</strong>
        </div>
        <div class="card-body">

            <?php $form = ActiveForm::begin([
                'action' => ['add-track', 'id' => $playlist->id],
                'method' => 'post',
            ]); ?>

            <div class="row">
                <div class="col-md-8">
                    <select name="track_id" class="form-control" required>
                        <option value="">-- Select track --</option>
                        <?php foreach ($availableTracks as $track): ?>
                            <option value="<?= $track->id ?>">
                                <?= Html::encode($track->title) ?>
                                (<?= Html::encode($track->artist->stage_name ?? 'unknown') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <?= Html::submitButton('Add track', ['class' => 'btn btn-success btn-block']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- ========================= -->
    <!-- Playlist tracks list -->
    <!-- ========================= -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => 'Track',
                'value' => fn($model) => $model->track->title ?? '-',
            ],
            [
                'label' => 'Artist',
                'value' => fn($model) => $model->track->artist->stage_name ?? '-',
            ],
            [
                'attribute' => 'position',
                'label' => 'Position',
            ],
            [
                'label' => 'Duration',
                'value' => function ($model) {
                    if (!$model->track || !$model->track->duration) {
                        return '-';
                    }

                    $seconds = $model->track->duration;
                    return gmdate('i:s', $seconds);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{remove}',
                'buttons' => [
                    'remove' => function ($url, $model) use ($playlist) {
                        return Html::a(
                            'Remove',
                            ['remove-track', 'id' => $playlist->id, 'track_id' => $model->track_id],
                            [
                                'class' => 'btn btn-sm btn-danger',
                                'data-confirm' => 'Remove this track from playlist?',
                                'data-method' => 'post',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]) ?>

</div>