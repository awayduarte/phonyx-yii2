<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Track $model */

$this->title = 'Update Track: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Tracks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="track-update">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
