<?php

use yii\helpers\Html;
use yii\helpers\Url;

// calcular displayName caso não venha passado
$identity = Yii::$app->user->identity ?? null;
if (!isset($displayName) && $identity) {
    $displayName = $identity->username
        ?? $identity->email
        ?? $identity->name
        ?? 'User';
}

?>
<header class="phonyx-navbar">

    <!-- LADO ESQUERDO: LOGO -->
    <div class="nav-left">
        <a href="<?= Url::to(['site/index']) ?>" class="nav-logo">
            <img src="<?= Url::to('@web/phonyx_logo_lateral.png') ?>" alt="Phonyx">
        </a>
    </div>

    <!-- CENTRO: MENU PRINCIPAL -->
    <nav class="nav-center">
        <a href="<?= Url::to(['site/index']) ?>"
            class="nav-link <?= Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index' ? 'active' : '' ?>">
            Home
        </a>

        <a href="<?= Url::to(['track/index']) ?>"
            class="nav-link <?= Yii::$app->controller->id === 'track' ? 'active' : '' ?>">
            Tracks
        </a>

        <a href="<?= Url::to(['site/about']) ?>"
            class="nav-link <?= Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'about' ? 'active' : '' ?>">
            About
        </a>

        <a href="<?= Url::to(['playlist/discover']) ?>"
            class="nav-link <?= Yii::$app->controller->id === 'playlist' && Yii::$app->controller->action->id === 'discover' ? 'active' : '' ?>">
            Playlists
        </a>


        <a href="<?= Url::to(['site/search']) ?>"
            class="nav-link <?= Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'search' ? 'active' : '' ?>">
            Search
        </a>
    </nav>

    <!-- DIREITA: AUTH / USER -->
    <div class="nav-right">
        <?php if (Yii::$app->user->isGuest): ?>

            <!-- GUEST: SIGN UP + LOGIN -->
            <a href="<?= Url::to(['site/signup']) ?>" class="btn-signup">Sign up</a>
            <a href="<?= Url::to(['site/login']) ?>" class="btn-login">Login</a>

        <?php else: ?>

            <!-- USER LOGADO: PILL COM DROPDOWN -->
            <div class="user-pill js-user-nav">
                <button class="user-trigger js-user-trigger">
                    <span class="pill-avatar">
                        <?= Html::encode(mb_strtoupper(mb_substr($displayName, 0, 1))) ?>
                    </span>
                    <span class="pill-name"><?= Html::encode($displayName) ?></span>
                    <span class="pill-caret">▾</span>
                </button>

                <div class="user-dropdown js-user-menu">
                    <a href="<?= Url::to(['user/profile']) ?>" class="user-dropdown-item">Perfil</a>
                    <a href="<?= Url::to(['user/settings']) ?>" class="user-dropdown-item">Definições</a>
                    <div class="user-dropdown-divider"></div>
                    <a href="<?= Url::to(['site/logout']) ?>" data-method="post" class="user-dropdown-item logout">
                        Terminar sessão
                    </a>
                </div>
            </div>

        <?php endif; ?>
    </div>
</header>