<?php
/** @var yii\web\View $this */
/** @var common\models\Artist $model */
/** @var common\models\Artist[] $followingArtists */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Following • ' . ($model->stage_name ?: 'Artist');

$defaultAvatar = Url::to('@web/img/default-avatar.png');

$resolveAssetUrl = function ($asset) use ($defaultAvatar) {
    if (!$asset || empty($asset->path)) return $defaultAvatar;

    $p = (string)$asset->path;
    if (preg_match('~^https?://~i', $p)) return $p;

    return Yii::getAlias('@web') . '/' . ltrim($p, '/');
};
?>

<div class="artist-page">

    <div class="sp-rowhead" style="margin-top:10px;">
        <h1 class="sp-rowtitle" style="font-size:28px;">A seguir</h1>
        <a class="sp-rowlink" href="<?= Url::to(['artist/view', 'id' => $model->id]) ?>">Voltar</a>
    </div>

    <?php if (empty($followingArtists)): ?>
        <p class="artist-empty">Not following any artists yet.</p>
    <?php else: ?>
        <div class="sp-grid">
            <?php foreach ($followingArtists as $a): ?>
                <?php
                    $aAvatar = $resolveAssetUrl($a->avatarAsset ?? null);
                    $aUrl = Url::to(['artist/view', 'id' => $a->id]);
                ?>

                <a class="sp-card sp-card--grid" href="<?= $aUrl ?>">
                    <div class="sp-card__avatar">
                        <img src="<?= Html::encode($aAvatar) ?>"
                             onerror="this.src='<?= Html::encode($defaultAvatar) ?>'"
                             alt="">
                    </div>

                    <div class="sp-card__name"><?= Html::encode($a->stage_name ?: 'Artist') ?></div>
                    <div class="sp-card__meta">Artista</div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
