<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * asset form
 *
 * @var yii\web\View $this
 * @var common\models\Asset $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="asset-form">

    <?php
    // multipart for file upload
    $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]);
    ?>

    <?php
    // file upload (image, audio)
    ?>
    <?= $form->field($model, 'file')->fileInput() ?>

    <?php
    // asset type
    ?>
    <?= $form->field($model, 'type')->dropDownList([
        'image' => 'image',
        'audio' => 'audio',
    ], [
        'prompt' => 'select type'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>