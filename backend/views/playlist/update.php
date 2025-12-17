<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Playlist $model */

$this->title = 'Update Playlist: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Playlists', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="playlist-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
