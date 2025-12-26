<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Criar Conta';

$this->registerCssFile('@web/css/signup.css');
?>

<div class="signup-wrapper">
    <div class="signup-card">
        <h1>Criar Conta</h1>

        <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

            <?= $form->field($model, 'username')->textInput(['placeholder' => 'Nome de utilizador']) ?>

            <?= $form->field($model, 'email')->input('email', ['placeholder' => 'Email']) ?>

            <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Palavra-passe']) ?>

            <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => 'Confirmar palavra-passe']) ?>

            <div class="form-group mt-3">
                <?= Html::submitButton('Criar conta', ['class' => 'btn btn-primary']) ?>
            </div>

        <?php ActiveForm::end(); ?>

        <div class="login-link">
            Já tens conta? <?= Html::a('Entrar', ['site/login']) ?>
        </div>
    </div>
</div>
