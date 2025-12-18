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

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'type',
            'path',

            [
                'label' => 'used in',
                'format' => 'raw',
                'value' => function (Asset $model) {
                    $used = [];

                    if ($model->users) {
                        $used[] = 'users';
                    }
                    if ($model->artists) {
                        $used[] = 'artists';
                    }
                    if ($model->albums) {
                        $used[] = 'albums';
                    }
                    if ($model->playlists) {
                        $used[] = 'playlists';
                    }
                    if ($model->tracks) {
                        $used[] = 'tracks';
                    }

                    return empty($used)
                        ? '<span class="badge badge-secondary">unused</span>'
                        : '<span class="badge badge-info">' . implode(', ', $used) . '</span>';
                },
            ],
            // 'created_at',

            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Asset $model) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>
</div>