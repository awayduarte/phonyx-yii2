<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * asset view
 *
 * @var yii\web\View $this
 * @var common\models\Asset $model
 */

$this->title = 'Asset #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="asset-view">

    <p>
        <?= Html::a('Atualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Apagar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'are you sure?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <!-- asset preview -->
    <div class="card mb-3">
        <div class="card-header">
            <strong>preview</strong>
        </div>
        <div class="card-body text-center">

            <?php if ($model->isImage()): ?>
                <?= Html::img(
                    '@web/' . $model->path,
                    [
                        'class' => 'img-fluid rounded',
                        'style' => 'max-height:300px'
                    ]
                ) ?>

            <?php elseif ($model->isAudio()): ?>
                <audio controls style="width:100%">
                    <source src="<?= Yii::getAlias('@web/' . $model->path) ?>" type="audio/mpeg">
                    your browser does not support audio playback
                </audio>

            <?php else: ?>
                <?= Html::a(
                    'download file',
                    '@web/' . $model->path,
                    ['class' => 'btn btn-outline-secondary']
                ) ?>
            <?php endif; ?>

        </div>
    </div>

    <!-- asset details -->
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'type',
            'path',
            'created_by_user_id',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>