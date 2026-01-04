<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Playlist $model */

$this->title = 'Criar playlist';
?>

<div class="container py-4">
    <h1 class="mb-4">Criar playlist</h1>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>
