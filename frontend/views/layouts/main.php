<?php

use frontend\assets\AppAsset;
use yii\helpers\Html;

// calcular displayName 
$identity = Yii::$app->user->identity ?? null;
if (!isset($displayName) && $identity) {
    $displayName = $identity->username
        ?? $identity->email
        ?? $identity->name
        ?? 'User';
}
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <?php
    // CSS principal da PHONYX
    $this->registerCssFile('@web/css/phonyx.css', [
        'depends' => [\yii\web\YiiAsset::class],
    ]);
    $this->registerCssFile('@web/css/artist.css', ['depends' => [\yii\web\YiiAsset::class]]);
    $this->registerCssFile('@web/css/artist-dashboard.css', ['depends' => [\yii\web\YiiAsset::class]]);
    

    ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="wrap">
    <?= $this->render('//partials/navbar') ?>

    <main class="phonyx-layout-content">
        <?= $content ?>
    </main>
</div>

<?php
$js = <<<JS
(function() {
  const nav = document.querySelector('.js-user-nav');
  if (!nav) return;

  const trigger = nav.querySelector('.js-user-trigger');
  const menu = nav.querySelector('.js-user-menu');

  function closeMenu() {
    nav.classList.remove('is-open');
  }

  function toggleMenu() {
    nav.classList.toggle('is-open');
  }

  trigger.addEventListener('click', function(e) {
    e.stopPropagation();
    toggleMenu();
  });

  // fechar ao clicar fora
  document.addEventListener('click', function(e) {
    if (!nav.contains(e.target)) {
      closeMenu();
    }
  });

  // fechar com ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeMenu();
    }
  });
})();
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>
<?php
echo $this->render('//partials/player');
?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
