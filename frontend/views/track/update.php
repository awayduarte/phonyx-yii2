<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Edit track | PHONYX';
?>

<div class="artist-auth-page">
  <div class="artist-auth-card">

    <h1 class="artist-auth-title">Edit track</h1>
    <p class="artist-auth-subtitle">Update the title, genre and optionally replace files.</p>

    <?php $form = ActiveForm::begin([
      'options' => ['enctype' => 'multipart/form-data'],
      'fieldConfig' => [
        'template' => "<div class=\"artist-auth-field\">{label}{input}<div class=\"artist-auth-error\">{error}</div></div>",
        'labelOptions' => ['class' => 'artist-auth-label'],
        'inputOptions' => ['class' => 'artist-auth-input form-control'],
      ],
    ]); ?>

      <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

      <?= $form->field($model, 'genre_id')->dropDownList($genreOptions, [
        'prompt' => 'Select genre',
        'class' => 'artist-auth-input',
      ]) ?>

      <?= $form->field($model, 'audioFile')->fileInput(['class' => 'artist-auth-input']) ?>
      <?= $form->field($model, 'coverFile')->fileInput(['class' => 'artist-auth-input']) ?>

      <div class="artist-auth-actions">
        <?= Html::submitButton('Save changes', ['class' => 'artist-auth-submit']) ?>
        <a href="<?= \yii\helpers\Url::to(['artist/dashboard']) ?>" class="artist-auth-cancel">Cancel</a>
      </div>

    <?php ActiveForm::end(); ?>

  </div>
</div>
