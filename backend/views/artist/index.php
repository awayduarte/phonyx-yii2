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
                'attribute' => 'user.username',
                'label' => 'User',
                'value' => fn(Artist $model) =>
                $model->user->username ?? '(deleted user)',
            ],
            'stage_name',

            [
                'label' => 'Music',
                'format' => 'raw',
                'value' => fn(Artist $model) =>
                Html::a(
                    'Tracks',
                    ['/track/index', 'TrackSearch[artist_id]' => $model->id],
                    ['class' => 'btn btn-sm btn-outline-primary']
                ),
            ],

            [
                'label' => 'Albums',
                'format' => 'raw',
                'value' => fn(Artist $model) =>
                Html::a(
                    'Albums',
                    ['/album/index', 'AlbumSearch[artist_id]' => $model->id],
                    ['class' => 'btn btn-sm btn-outline-primary']
                ),
            ],

            [
                'class' => ActionColumn::class,
                'urlCreator' => fn($action, Artist $model) =>
                Url::toRoute([$action, 'id' => $model->id]),
            ],
        ],
    ]); ?>

</div>