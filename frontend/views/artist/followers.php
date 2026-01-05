<?php
/** @var yii\web\View $this */
/** @var common\models\Artist $model */
/** @var common\models\User[] $followers */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Followers • ' . ($model->stage_name ?: 'Artist');

$defaultAvatar = Url::to('@web/img/default-avatar.png');

$assetUrl = function ($path) use ($defaultAvatar) {
    if (!$path) return $defaultAvatar;
    if (preg_match('~^https?://~i', $path)) return $path;
    return Yii::getAlias('@web') . '/' . ltrim($path, '/');
};
?>

<div class="artist-page">

    <div class="sp-rowhead" style="margin-top:10px;">
        <h1 class="sp-rowtitle" style="font-size:28px;">Seguidores</h1>
        <a class="sp-rowlink" href="<?= Url::to(['artist/view', 'id' => $model->id]) ?>">Voltar</a>
    </div>

    <?php if (empty($followers)): ?>
        <p class="artist-empty">No followers yet.</p>
    <?php else: ?>
        <div class="sp-grid">
            <?php foreach ($followers as $u): ?>
                <?php
                    $uAvatarPath = $u->profileAsset->path ?? null;
                    $uAvatar = $assetUrl($uAvatarPath);
                    $uUrl = Url::to(['profile/view', 'id' => $u->id]);
                ?>

                <a class="sp-card sp-card--grid" href="<?= $uUrl ?>">
                    <div class="sp-card__avatar">
                        <img src="<?= Html::encode($uAvatar) ?>"
                             onerror="this.src='<?= Html::encode($defaultAvatar) ?>'"
                             alt="">
                    </div>

                    <div class="sp-card__name"><?= Html::encode($u->username) ?></div>
                    <div class="sp-card__meta">Perfil</div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
