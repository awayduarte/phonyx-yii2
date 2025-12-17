<?php

use common\models\Artist;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var backend\models\ArtistSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Artists';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="artist-index">

    <p>
        <?= Html::a('Create Artist', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',

            [
                'attribute' => 'user_id',
                'label' => 'User',
                'value' => fn($model) => $model->user->username ?? '(deleted user)',
            ],

            'stage_name',

            [
                'attribute' => 'bio',
                'format' => 'ntext',
                'contentOptions' => ['style' => 'max-width:300px; white-space:normal;'],
            ],

            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Artist $model) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]); ?>

</div>