<?php

use common\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var backend\models\UserSearch $searchModel */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">
    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(
            'View deleted users',
            ['deleted'],
            ['class' => 'btn btn-outline-danger ml-2']
        ) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            'email:email',
            [
                'attribute' => 'status',
                'filter' => [
                    10 => 'Active',
                    0  => 'Inactive',
                ],
                'value' => fn($model) => $model->status == 10 ? 'Active' : 'Inactive',
            ],
            'role',

            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, User $model) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

</div>