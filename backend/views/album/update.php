<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Album $model */

$this->title = 'Update Album: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Albums', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="album-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
