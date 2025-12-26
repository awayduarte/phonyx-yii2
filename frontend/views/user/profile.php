<?php
/** @var yii\web\View $this */
/** @var \common\models\User $user */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Perfil | PHONYX';
$this->registerCssFile(
    Yii::getAlias('@web/css/profile.css'),
    ['depends' => [\frontend\assets\AppAsset::class]]
);

$displayName = $user->username ?? $user->email ?? 'User';
$email = $user->email ?? 'Sem email';
$createdAt = $user->created_at ?? null;
?>
<div class="profile-page">

    <section class="profile-header">
    <?php
// Pick user avatar (fallback to default)
$avatarUrl = Yii::getAlias('@web') . '/img/default-avatar.png';

if (!empty($user->profile_asset_id) && $user->profileAsset && !empty($user->profileAsset->path)) {
    $avatarUrl = Yii::getAlias('@web') . $user->profileAsset->path;
}
?>

<div class="profile-avatar profile-avatar--img">
    <img src="<?= \yii\helpers\Html::encode($avatarUrl) ?>" alt="Avatar">
</div>

        <!-- BLOCO DA DIREITA: info + botão artista -->
        <div class="profile-header-main">

            <div class="profile-main">
                <span class="profile-label">PERFIL</span>
                <h1 class="profile-name">
                    <?= Html::encode($displayName) ?>
                </h1>

                <div class="profile-meta-row">
                    <span class="profile-meta-item">
                        <?= Html::encode($email) ?>
                    </span>

                    <?php if ($createdAt): ?>
                        <span class="profile-dot">•</span>
                        <span class="profile-meta-item">
                            Na PHONYX desde
                            <?= Yii::$app->formatter->asDate($createdAt) ?>
                        </span>
                    <?php endif; ?>

                    <?php if (Yii::$app->user->can('admin')): ?>
                        <span class="profile-dot">•</span>
                        <span class="profile-badge profile-badge-admin">
                            ADMIN
                        </span>
                    <?php endif; ?>
                </div>

                <div class="profile-actions">
                    <a href="<?= Url::to(['user/edit-profile']) ?>"
                       class="btn btn-accent profile-btn-main">
                        Editar perfil
                    </a>
                    <button type="button" class="btn btn-ghost profile-btn-secondary">
                        Ver estatísticas
                    </button>
                </div>
            </div>

            <!-- BOTÃO GRANDE DE ARTISTA À DIREITA -->
            <div class="profile-artist-button">
                <?php if ($user->role === 'artist'): ?>
                    <a href="<?= Url::to(['artist/create']) ?>" class="btn-artist-big">
                        Criar conta de artista
                    </a>
                <?php else: ?>
                    <a href="<?= Url::to(['artist/dashboard']) ?>" class="btn-artist-big">
                        Painel de artista
                    </a>
                <?php endif; ?>
                <a href="<?= \yii\helpers\Url::to(['site/index']) ?>" class="artist-dash-back">
    ← Voltar ao home
</a>

            </div>

        </div>
    </section>

    <section class="profile-grid">

        <div class="profile-card">
            <h2>Atividade recente</h2>
            <p class="profile-card-text">
                Em breve vais conseguir ver as tuas últimas faixas ouvidas, uploads
                recentes e interações na comunidade PHONYX.
            </p>
            <ul class="profile-list">
                <li>Histórico de reprodução</li>
                <li>Últimos uploads como artista</li>
                <li>Playlists que criaste ou seguiste</li>
            </ul>
        </div>

        <div class="profile-card">
            <h2>Tipo de conta</h2>
            <p class="profile-card-text">
                Por agora a conta é genérica. No futuro vais poder escolher se és
                <strong>ouvinte</strong>, <strong>artista</strong> ou ambos.
            </p>
            <div class="profile-pill-row">
                <span class="profile-pill active">Ouvinte</span>
                <span class="profile-pill">Artista</span>
                <span class="profile-pill">Ambos</span>
            </div>
        </div>

        <div class="profile-card">
            <h2>Segurança & sessão</h2>
            <p class="profile-card-text">
                Gestão de sessão, logout em todos os dispositivos e atualização de palavra-passe.
            </p>
            <div class="profile-security-row">
                <span class="profile-status-dot online"></span>
                <span class="profile-security-text">Sessão ativa neste dispositivo</span>
            </div>
            <button type="button" class="btn btn-ghost profile-btn-secondary">
                Terminar sessão em todos os dispositivos
            </button>
        </div>

    </section>
</div>
