<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\TrackSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="track-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'artist_id') ?>

    <?= $form->field($model, 'album_id') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'audio_asset_id') ?>

    <?php // echo $form->field($model, 'duration') ?>

    <?php // echo $form->field($model, 'genre_id') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
