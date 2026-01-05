<?php

use common\models\Playlist;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var backend\models\PlaylistSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Playlists';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="playlist-index">

    <p>
        <?= Html::a('Create Playlist', ['create'], ['class' => 'btn btn-success']) ?>
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
                'value' => fn($model) => $model->user->username ?? '(deleted)',
            ],

            'title',

            [
                'attribute' => 'description',
                'format' => 'ntext',
                'enableSorting' => false,
            ],

            'cover_asset_id',

            [
                'class' => ActionColumn::class,
                'template' => '{view} {tracks} {update} {delete}',
                'buttons' => [
                    'tracks' => fn($url, Playlist $model) =>
                    Html::a(
                        '<i class="fas fa-music"></i>',
                        ['playlist/tracks', 'id' => $model->id],
                        [
                            'title' => 'View tracks',
                            'class' => 'text-primary',
                            'data-pjax' => '0',
                        ]
                    ),
                ],

            ],
        ],
    ]); ?>


</div>