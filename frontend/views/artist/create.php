<?php
/** @var yii\web\View $this */
/** @var common\models\Artist $model */
/** @var common\models\User $user */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Criar conta de artista | PHONYX';
?>

<div class="artist-auth-page">

    <div class="artist-auth-card">
        <h1 class="artist-auth-title">Criar conta de artista</h1>
        <p class="artist-auth-subtitle">
            Define o teu nome artístico e os links principais — podes editar tudo mais tarde
            no painel de artista.
        </p>

        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'artist-auth-form'],
            'fieldConfig' => [
                'template' =>
                    "<div class=\"artist-auth-field\">
                        {label}
                        {input}
                        <div class=\"artist-auth-error\">{error}</div>
                    </div>",
                'labelOptions' => ['class' => 'artist-auth-label'],
                'inputOptions' => ['class' => 'artist-auth-input form-control'],
            ],
        ]); ?>

            <?= $form->field($model, 'stage_name')->textInput([
    'maxlength' => true,
    'placeholder' => 'Nome artístico'
])
?>

            <?= $form->field($model, 'bio')
                ->textarea([
                    'rows' => 3,
                    'placeholder' => 'Fala um pouco sobre o teu projeto musical, influências e vibe.',
                    'class' => 'artist-auth-input artist-auth-textarea form-control',
                ]); ?>
            <div class="artist-auth-actions">
                <?= Html::submitButton('Criar conta de artista', [
                    'class' => 'artist-auth-submit',
                ]) ?>
            </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
