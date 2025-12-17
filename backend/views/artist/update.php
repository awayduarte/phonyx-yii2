<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Artist $model */
/** @var common\models\User[] $users */

$this->title = 'Update Artist: ' . $model->stage_name;
$this->params['breadcrumbs'][] = ['label' => 'Artists', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->stage_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="artist-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'users' => $users,
    ]) ?>

</div>