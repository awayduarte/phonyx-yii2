<?php
/** @var yii\web\View $this */
/** @var \frontend\models\EditProfileForm $model */
/** @var \common\models\User $user */
/** @var common\models\Artist $model */


use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

$this->title = 'Editar perfil | PHONYX';
$this->registerCssFile(
    Yii::getAlias('@web/css/profile.css'),
    ['depends' => [\frontend\assets\AppAsset::class]]
);


$avatarUrl = Yii::getAlias('@web') . '/img/default-avatar.png';
if ($user->profileAsset && !empty($user->profileAsset->path)) {
    $avatarUrl = Yii::getAlias('@web') . $user->profileAsset->path;
}
?>

<div class="profile-page edit-profile-page">

    <section class="profile-header">
        <div class="profile-avatar profile-avatar--img">
            <img id="profile-photo-preview"
                 src="<?= Html::encode($avatarUrl) ?>"
                 alt="Profile photo">
        </div>

        <div class="profile-main">
            <span class="profile-label">PERFIL</span>
            <h1 class="profile-name">Editar perfil</h1>

            <div class="profile-meta-row">
                <span class="profile-meta-item">
                    <?= Html::encode($user->email ?? '') ?>
                </span>
                <span class="profile-dot">•</span>
            </div>

            <div class="profile-actions">
                <a href="<?= Url::to(['user/profile']) ?>" class="btn btn-ghost profile-btn-secondary">
                    ← Voltar ao perfil
                </a>
            </div>
        </div>
    </section>

    <section class="edit-profile-form-section">
        <div class="settings-card">
            <h2>Informação básica</h2>
            <p class="settings-card-text">
                Atualiza o nome de utilizador, o email e a foto de perfil.
            </p>

            <?php $form = ActiveForm::begin([
                'options' => [
                    'class' => 'settings-form',
                
                    'enctype' => 'multipart/form-data',
                ],
            ]); ?>

            <div class="settings-field">
                <?= $form->field($model, 'username')
                    ->label('Nome de utilizador')
                    ->textInput(['maxlength' => true]) ?>
            </div>

            <div class="settings-field">
                <?= $form->field($model, 'email')
                    ->label('Email')
                    ->textInput(['maxlength' => true]) ?>
            </div>

           
            <div class="settings-field">
                <label class="form-label">Foto de perfil</label>

                <div class="profile-upload">
                    <button type="button" class="btn btn-ghost profile-upload-btn" id="profile-upload-btn">
                        Choose photo
                    </button>

                    <div class="profile-upload-filename" id="profile-upload-filename">
                        No file selected
                    </div>
                </div>

                <div class="profile-upload-hint">
                    PNG, JPG, JPEG or WEBP • up to 5MB
                </div>

                <?= $form->field($model, 'profileFile')
                    ->label(false)
                    ->fileInput([
                        'id' => 'profile-file-input',
                        'class' => 'profile-file-input-hidden'
                    ]) ?>
            </div>

                        
            <div class="settings-actions">
                <button type="submit" class="btn btn-accent settings-btn-save">
                    Save changes
                </button>
            </div>

            <?php ActiveForm::end(); ?>

            <p class="settings-help">
                No futuro podes adicionar também uma biografia curta e links de redes sociais.
            </p>
        </div>
    </section>
</div>

<?php

$this->registerJs(<<<JS
(function(){
  var btn = document.getElementById('profile-upload-btn');
  var input = document.getElementById('profile-file-input');
  var filename = document.getElementById('profile-upload-filename');
  var preview = document.getElementById('profile-photo-preview');

  if (btn && input) {
    btn.addEventListener('click', function(){
      input.click();
    });
  }

  if (input) {
    input.addEventListener('change', function(){
      var file = input.files && input.files[0] ? input.files[0] : null;
      filename.textContent = file ? file.name : 'No file selected';

    
      if (file && preview && file.type && file.type.indexOf('image/') === 0) {
        var url = URL.createObjectURL(file);
        preview.src = url;
      }
    });
  }
})();
JS);
?>