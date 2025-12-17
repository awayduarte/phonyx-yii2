<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var common\models\Playlist $playlist */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Tracks – ' . $playlist->title;
$this->params['breadcrumbs'][] = ['label' => 'Playlists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="playlist-tracks">

    <p>
        <?= Html::a('← Back to playlists', ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => 'Track',
                'value' => fn($model) => $model->track->title ?? '-',
            ],
            [
                'label' => 'Artist',
                'value' => fn($model) => $model->track->artist->stage_name ?? '-',
            ],
            [
                'attribute' => 'position',
            ],
            [
                'label' => 'Duration',
                'value' => fn($model) => $model->track->duration ?? '-',
            ],
        ],
    ]) ?>

</div>