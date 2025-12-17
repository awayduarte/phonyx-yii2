<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Playlist $model */

$this->title = 'Create Playlist';
$this->params['breadcrumbs'][] = ['label' => 'Playlists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="playlist-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
