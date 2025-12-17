<?php

use hail812\adminlte3\assets\AdminLteAsset;
use hail812\adminlte3\assets\FontAwesomeAsset;
use yii\helpers\Html;
use yii\helpers\Url;

AdminLteAsset::register($this);
FontAwesomeAsset::register($this);
$this->registerCssFile('@web/css/site.css', [
    'depends' => [AdminLteAsset::class],
]);

// RBAC check
$isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->can('admin');

// current route
$route = Yii::$app->controller->route;

// active helpers
$isDashboard = ($route === 'site/index');
$isUser      = strpos($route, 'user/') === 0;
$isArtist    = strpos($route, 'artist/') === 0;
$isGenre     = strpos($route, 'genre/') === 0;
$isAlbum     = strpos($route, 'album/') === 0;
$isTrack = strpos($route, 'track/') === 0;
$isAsset = strpos($route, 'asset/') === 0;
$isPlaylist = strpos($route, 'playlist/') === 0;
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="hold-transition sidebar-mini">
    <?php $this->beginBody() ?>

    <div class="wrapper">

        <!-- NAVBAR -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="nav-link">
                        <?= Html::encode(Yii::$app->user->identity->username ?? '') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <?= Html::beginForm(['/site/logout'], 'post') ?>
                    <?= Html::submitButton('Logout', ['class' => 'btn btn-link nav-link']) ?>
                    <?= Html::endForm() ?>
                </li>
            </ul>
        </nav>

        <!-- SIDEBAR -->
        <?php if ($isAdmin): ?>
            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <a href="<?= Url::to(['/site/index']) ?>" class="brand-link">
                    <!-- logo -->
                    <img
                        src="<?= Url::to('@web/images/logo') ?>"
                        alt="Phonyx Logo"
                        class="brand-image img-circle elevation-3"
                        style="opacity: .8">

                    <!-- expanded text -->
                    <span class="brand-text font-weight-light">Phonyx Admin</span>
                </a>

                <div class="sidebar">
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column">

                            <li class="nav-item">
                                <a href="<?= Url::to(['/site/index']) ?>"
                                    class="nav-link <?= $isDashboard ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= Url::to(['/user/index']) ?>"
                                    class="nav-link <?= $isUser ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Users</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= Url::to(['/artist/index']) ?>"
                                    class="nav-link <?= $isArtist ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-microphone"></i>
                                    <p>Artists</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= Url::to(['/track/index']) ?>"
                                    class="nav-link <?= $isTrack ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-music"></i>
                                    <p>Tracks</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= Url::to(['/album/index']) ?>"
                                    class="nav-link <?= $isAlbum ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-compact-disc"></i>
                                    <p>Albums</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= Url::to(['/playlist/index']) ?>"
                                    class="nav-link <?= $isPlaylist ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-list-ul"></i>
                                    <p>Playlists</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= Url::to(['/genre/index']) ?>"
                                    class="nav-link <?= $isGenre ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>Genres</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="<?= Url::to(['/asset/index']) ?>"
                                    class="nav-link <?= $isAsset ? 'active' : '' ?>">
                                    <i class="nav-icon fas fa-file-audio"></i>
                                    <p>Assets</p>
                                </a>
                            </li>

                        </ul>
                    </nav>
                </div>
            </aside>
        <?php endif; ?>

        <!-- CONTENT -->
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <h1><?= Html::encode($this->title) ?></h1>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <?= $content ?>
                </div>
            </section>
        </div>

        <!-- FOOTER -->
        <footer class="main-footer text-sm">
            <strong>&copy; <?= date('Y') ?> PHONYX</strong>
            <div class="float-right d-none d-sm-inline-block">Admin Panel</div>
        </footer>

    </div>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>