<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Track $model */

$this->title = 'Create Track';
$this->params['breadcrumbs'][] = ['label' => 'Tracks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="track-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
