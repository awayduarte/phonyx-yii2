<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var common\models\Playlist $model */
?>

<div class="playlist-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput([
        'maxlength' => true,
        'placeholder' => 'Nome da playlist'
    ]) ?>

    <?= $form->field($model, 'description')->textarea([
        'rows' => 4,
        'placeholder' => 'Descrição (opcional)'
    ]) ?>

    <div class="mt-3">
        <?= Html::submitButton('Criar', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['playlist/discover'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
