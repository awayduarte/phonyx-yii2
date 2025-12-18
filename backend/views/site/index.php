<?php

use yii\helpers\Html;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var array $stats
 */

// Chart.js
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/chart.js',
    ['position' => View::POS_HEAD]
);

// Dashboard JS
$this->registerJsFile(
    '@web/js/dashboard.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->title = 'Admin Dashboard';
?>

<div class="site-index">

    <!-- cards -->
    <div class="row">

        <?php
        $boxes = [
            ['label' => 'Utilizadores', 'value' => $stats['users'],   'icon' => 'users',      'color' => 'info',    'url' => ['/user/index']],
            ['label' => 'Artistas',     'value' => $stats['artists'], 'icon' => 'microphone', 'color' => 'success', 'url' => ['/artist/index']],
            ['label' => 'Faixas',       'value' => $stats['tracks'],  'icon' => 'music',      'color' => 'warning', 'url' => ['/track/index']],
        ];
        ?>

        <?php foreach ($boxes as $box): ?>
            <div class="col-lg-4 col-12">
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

    <div class="row">
        <!-- donut chart -->
        <div class="col-lg-6">
            <div class="card card-outline card-info h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        Distribuição de Conteúdo
                    </h3>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas
                        id="contentChart"
                        style="max-width: 320px; max-height: 320px;"
                        data-chart='<?= json_encode([
                                        $stats['tracks'] ?? 0,
                                        $stats['albums'] ?? 0,
                                        $stats['playlists'] ?? 0
                                    ]) ?>'>
                    </canvas>
                </div>
            </div>
        </div>

        <!-- quick actions -->
        <div class="col-lg-6">
            <div class="card card-outline card-secondary h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i>
                        Ações Rápidas
                    </h3>
                </div>

                <div class="card-body">

                    <!-- create -->
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-plus-circle"></i> Criar
                    </h6>
                    <div class="row text-center mb-3">
                        <div class="col-6 mb-2">
                            <?= Html::a(
                                '<i class="fas fa-user-plus fa-lg"></i><br>Utilizador',
                                ['/user/create'],
                                ['class' => 'btn btn-outline-primary w-100']
                            ) ?>
                        </div>
                        <div class="col-6 mb-2">
                            <?= Html::a(
                                '<i class="fas fa-microphone-alt fa-lg"></i><br>Artista',
                                ['/artist/create'],
                                ['class' => 'btn btn-outline-success w-100']
                            ) ?>
                        </div>
                        <div class="col-6">
                            <?= Html::a(
                                '<i class="fas fa-music fa-lg"></i><br>Faixa',
                                ['/track/create'],
                                ['class' => 'btn btn-outline-warning w-100']
                            ) ?>
                        </div>
                        <div class="col-6">
                            <?= Html::a(
                                '<i class="fas fa-tags fa-lg"></i><br>Género',
                                ['/genre/create'],
                                ['class' => 'btn btn-outline-info w-100']
                            ) ?>
                        </div>
                    </div>

                    <!-- manage -->
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-sliders-h"></i> Gerir
                    </h6>
                    <div class="row text-center mb-3">
                        <div class="col-6 mb-2">
                            <?= Html::a(
                                '<i class="fas fa-list fa-lg"></i><br>Playlists',
                                ['/playlist/index'],
                                ['class' => 'btn btn-outline-secondary w-100']
                            ) ?>
                        </div>
                        <div class="col-6 mb-2">
                            <?= Html::a(
                                '<i class="fas fa-compact-disc fa-lg"></i><br>Álbuns',
                                ['/album/index'],
                                ['class' => 'btn btn-outline-dark w-100']
                            ) ?>
                        </div>
                    </div>

                    <!-- maintenece -->
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-tools"></i> Manutenção
                    </h6>
                    <div class="row text-center">
                        <div class="col-12">
                            <?= Html::a(
                                '<i class="fas fa-folder-open fa-lg"></i><br>Gerir Assets',
                                ['/asset/index'],
                                ['class' => 'btn btn-outline-dark w-100']
                            ) ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>