<?php
/** @var yii\web\View $this */
/** @var \common\models\Artist $model */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

$this->title = 'Edit artist profile | PHONYX';

/**
 * Load a dedicated stylesheet for this page only.
 * Keep CSS out of the view (clean Yii approach).
 */
$this->registerCssFile(
    Yii::getAlias('@web/css/artist-edit.css'),
    ['depends' => [\frontend\assets\AppAsset::class]]
);
?>

<div class="artist-edit-page">

    <section class="artist-edit-header">
        <div class="artist-edit-header-left">
            <span class="artist-edit-chip">Artist settings</span>
            <h1 class="artist-edit-title">Edit artist profile</h1>
            <p class="artist-edit-subtitle">
                Update your stage name and bio. Changes will appear on your public artist page.
            </p>
        </div>

        <div class="artist-actions">

            <a href="<?= Url::to(['artist/edit']) ?>" class="artist-btn artist-btn-primary">
                Edit artist profile
            </a>

            <a href="<?= Url::to(['artist/dashboard']) ?>" class="artist-btn artist-btn-secondary">
                ← Back to dashboard
            </a>

            <a href="<?= Url::to(['artist/view', 'id' => $artist->id]) ?>" class="artist-btn artist-btn-secondary">
                View public profile
            </a>

        </div>


    </section>

    <section class="artist-edit-card">
        <?php $form = ActiveForm::begin([
            'options' => ['class' => 'artist-edit-form'],
            'fieldConfig' => [
                'template' =>
                    "<div class=\"artist-edit-field\">
                        {label}
                        {input}
                        <div class=\"artist-edit-error\">{error}</div>
                    </div>",
                'labelOptions' => ['class' => 'artist-edit-label'],
                'inputOptions' => ['class' => 'artist-edit-input form-control'],
            ],
        ]); ?>

        <?= $form->field($model, 'stage_name')
            ->textInput([
                'maxlength' => true,
                'placeholder' => 'Your stage name',
            ]); ?>

        <?= $form->field($model, 'bio')
            ->textarea([
                'rows' => 6,
                'class' => 'artist-edit-input artist-edit-textarea form-control',
                'placeholder' => 'Write a short bio (optional)',
            ]); ?>

        <div class="artist-edit-actions">
            <?= Html::submitButton('Save changes', [
                'class' => 'artist-edit-save',
            ]) ?>

            <a href="<?= Url::to(['artist/dashboard']) ?>" class="artist-edit-cancel">
                Cancel
            </a>
        </div>

        <?php ActiveForm::end(); ?>
    </section>

</div>