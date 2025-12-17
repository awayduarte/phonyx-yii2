<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password')->passwordInput()->hint(
        $model->isNewRecord
            ? 'Set initial password'
            : 'Leave empty to keep current password'
    ) ?>

    <?= $form->field($model, 'role')->dropDownList([
        'admin'  => 'Admin',
        'artist' => 'Artist',
        'user'   => 'User',
    ], ['prompt' => 'Select role']) ?>

    <?= $form->field($model, 'status')->dropDownList([
        10 => 'Active',
        0  => 'Inactive',
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? 'Create' : 'Update',
            ['class' => 'btn btn-success']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>