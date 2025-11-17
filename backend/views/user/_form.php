<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- Email -->
    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <!-- Username -->
    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <!-- Display name -->
    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>

    <!-- Password -->
    <?= $form->field($model, 'password_plain')
        ->passwordInput(['maxlength' => true])
        ->hint($model->isNewRecord
            ? 'Introduz uma password.'
            : 'Deixa vazio para manter a password atual.'
        ) ?>

    <!-- Role -->
    <?= $form->field($model, 'role')->dropDownList([
        'admin'  => 'Admin',
        'artist' => 'Artist',
        'user'   => 'User',
    ], ['prompt' => 'Escolhe um role']) ?>

    <!-- Status -->
    <?= $form->field($model, 'status')->dropDownList([
        0 => 'ACTIVE',
        1 => 'SUSPENDED',
        2 => 'DELETED',
    ]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton(
            $model->isNewRecord ? 'Create' : 'Update',
            ['class' => 'btn btn-success']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
