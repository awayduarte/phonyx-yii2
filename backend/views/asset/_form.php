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

$this->registerJsFile('@web/js/asset.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);

?>

<div class="asset-form">

    <?php
    // start form with multipart for file upload
    $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]);
    ?>

    <?php
    // custom styled file input 
    ?>
    <?= $form->field($model, 'file', [
        'template' => "{label}
        <div class=\"custom-file\">
            {input}
            <label class=\"custom-file-label\">choose file</label>
        </div>
        {error}",
    ])->fileInput([
        'class' => 'custom-file-input',
        'accept' => 'image/*,audio/*',
    ]) ?>

    <?php
    // submit button
    ?>
    <div class="form-group mt-3">
        <?= Html::submitButton('Salvar', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary ml-2']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>