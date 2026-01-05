<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Artist $model */
/** @var common\models\User[] $users */

$this->title = 'Create Artist';
$this->params['breadcrumbs'][] = ['label' => 'Artists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="artist-create">

    <?= $this->render('_form', [
        'model' => $model,
        'users' => $users,
    ]) ?>

</div>
