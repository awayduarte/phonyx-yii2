<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Genre $model */

$this->title = 'Update Genre: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Genres', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="genre-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
