<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Asset $model */

$this->title = 'Create Asset';
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-create">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
