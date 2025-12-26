<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\LoginForm $model */

$this->title = 'Login';

/**
 * CSS específico do login (ficheiro externo)
 */
$this->registerCssFile('@web/css/login.css', [
    'depends' => [\yii\bootstrap5\BootstrapAsset::class],
]);

/**
 * JS: faz o vinil rodar quando se submete o login
 */
$this->registerJs(<<<JS
const form = document.getElementById("loginForm");
const vinyl = document.getElementById("vinyl");

if (form && vinyl) {
  form.addEventListener("submit", function () {
    vinyl.classList.add("spin");
  });
}
JS);
?>

<div class="login-page">
    <div class="login-card">
        <div class="login-left">
            <div class="brand">
                <div class="brand-icon">Φ</div>
                <div>
                    <div class="brand-title">PHONYX</div>
                    <div class="brand-sub">MUSIC PLATFORM</div>
                </div>
            </div>

            <h1 class="login-title">Bem-vindo de volta</h1>
            <p class="login-subtitle">
                Entra na tua conta e volta às tuas playlists e artistas favoritos.
            </p>

            <?php $form = ActiveForm::begin([
                'id' => 'loginForm',
                'options' => ['class' => 'login-form'],
            ]); ?>

            <?= $form->field($model, 'email')
                ->textInput([
                    'autofocus' => true,
                    'placeholder' => 'O teu username ou email',
                ])
                ->label('Username ou Email') ?>

            <?= $form->field($model, 'password')
                ->passwordInput([
                    'placeholder' => '••••••••',
                ])
                ->label('Palavra-passe') ?>

            <div class="form-row">
                <label class="remember">
                    <?= Html::checkbox(
                        Html::getInputName($model, 'rememberMe'),
                        $model->rememberMe,
                        ['value' => 1]
                    ) ?>
                    <span>Lembrar-me</span>
                </label>
                <a href="#" class="link">Esqueceste-te da palavra-passe?</a>
            </div>

            <button type="submit" class="btn-login" name="login-button">
                <span class="icon">▶</span>
                <span>Login</span>
            </button>

            <?php ActiveForm::end(); ?>

            <p class="login-footer">
                Ainda não tens conta?
                <a href="<?= \yii\helpers\Url::to(['site/signup']) ?>" class="link">Criar conta</a>
            </p>
        </div>

        <div class="login-right">
            <div class="vinyl-wrapper">
                <div class="vinyl-shadow">
                    <div class="vinyl" id="vinyl">
                        <div class="vinyl-grooves"></div>
                        <div class="vinyl-label">
                            <span class="vinyl-logo">PHONYX</span>
                            <span class="vinyl-sub">STEREO</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
