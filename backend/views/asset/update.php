<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Asset $model */

$this->title = 'Update Asset: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="asset-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
