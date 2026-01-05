<?php

use common\models\Asset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var backend\models\AssetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Assets';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="asset-index">

    <p>
        <?= Html::a('Create Asset', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,

        'pager' => [
            'class' => \yii\bootstrap4\LinkPager::class,
            'options' => ['class' => 'pagination justify-content-center'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link'],
            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
        ],

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'id',
            ],
            [
                'attribute' => 'type',
                'filter' => [
                    'image' => 'image',
                    'audio' => 'audio',
                    'video' => 'video',
                    'other' => 'other',
                ],
            ],
            [
                'attribute' => 'path',
            ],

            [
                'attribute' => 'used_count',
                'label' => 'Used in',
                'format' => 'raw',
                'value' => fn(Asset $model) =>
                $model->used_count == 0
                    ? '<span class="badge badge-secondary">unused</span>'
                    : '<span class="badge badge-info">' . $model->used_count . '</span>',
            ],

            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Asset $model) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]); ?>

</div>