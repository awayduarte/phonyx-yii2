<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Genre $model */

$this->title = 'Create Genre';
$this->params['breadcrumbs'][] = ['label' => 'Genres', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="genre-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
