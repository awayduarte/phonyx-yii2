<?php

use yii\helpers\Html;
use yii\web\View;

/** @var yii\web\View $this */
/** @var array $stats */

// External chart library (Chart.js)
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/chart.js',
    ['position' => View::POS_HEAD]
);

$this->registerJsFile(
    '@web/js/dashboard.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->title = 'Admin Dashboard';
?>

<div class="site-index">

    <!-- Cards -->
    <div class="row">

        <?php
        $boxes = [
            ['label' => 'Utilizadores', 'value' => $stats['users'],     'icon' => 'users',          'color' => 'info',     'url' => ['/user/index']],
            ['label' => 'Artistas',     'value' => $stats['artists'],   'icon' => 'user-music',     'color' => 'success',  'url' => ['/artist/index']],
            ['label' => 'Faixas',       'value' => $stats['tracks'],    'icon' => 'music',          'color' => 'warning',  'url' => ['/track/index']],
            ['label' => 'Álbuns',       'value' => $stats['albums'],    'icon' => 'compact-disc',  'color' => 'secondary', 'url' => ['/album/index']],
            ['label' => 'Playlists',    'value' => $stats['playlists'], 'icon' => 'list',           'color' => 'danger',   'url' => ['/playlist/index']],
            ['label' => 'Géneros',      'value' => $stats['genres'],    'icon' => 'tags',           'color' => 'primary',  'url' => ['/genre/index']],
            ['label' => 'Assets',       'value' => $stats['assets'],    'icon' => 'folder-open',   'color' => 'dark',     'url' => ['/asset/index']],
        ];
        ?>

        <?php foreach ($boxes as $box): ?>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-<?= $box['color'] ?>">
                    <div class="inner">
                        <h3><?= $box['value'] ?></h3>
                        <p><?= Html::encode($box['label']) ?></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-<?= $box['icon'] ?>"></i>
                    </div>
                    <?= Html::a('Gerir', $box['url'], ['class' => 'small-box-footer']) ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- Donut chart -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Distribuição de Conteúdo
                    </h3>
                </div>
                <div class="card-body">
                    <canvas
                        id="contentChart"
                        data-chart='<?= json_encode([
                                        $stats['tracks'] ?? 0,
                                        $stats['albums'] ?? 0,
                                        $stats['playlists'] ?? 0
                                    ]) ?>'>
                    </canvas>
                </div>
            </div>
        </div>
    </div>
</div>