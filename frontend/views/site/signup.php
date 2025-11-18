<?php

/** @var yii\web\View $this */
/** @var frontend\models\SignupForm $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Criar Conta';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup container py-5">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>Preenche os dados para criares a tua conta Phonyx.</p>

    <div class="row mt-4">
        <div class="col-md-6">

            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Username']) ?>

                <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email']) ?>

                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password']) ?>

                <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => 'Confirmar Password']) ?>

                <div class="form-group mt-3">
                    <?= Html::submitButton('Criar Conta', ['class' => 'btn btn-primary btn-lg btn-block']) ?>
                </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

</div>
