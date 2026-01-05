<?php
/** @var yii\web\View $this */
/** @var common\models\Artist $model */
/** @var common\models\Playlist[] $playlists */
/** @var common\models\User[] $followers */
/** @var common\models\Artist[] $followingArtists */


use yii\helpers\Html;
use yii\helpers\Url;

$this->title = ($model->stage_name ?: 'Artist') . ' | PHONYX';

$resolveAssetUrl = function ($asset, $fallbackWebPath) {
    $fallback = Yii::getAlias('@web') . '/' . ltrim($fallbackWebPath, '/');

    if (!$asset || empty($asset->path)) {
        return $fallback;
    }

    $p = (string) $asset->path;


    if (preg_match('~^https?://~i', $p)) {
        return $p;
    }


    return Yii::getAlias('@web') . '/' . ltrim($p, '/');
};


$avatarUrl = $resolveAssetUrl($model->avatarAsset ?? null, 'img/default-avatar.png');


$user = $model->user ?? null;


$isFollowing = false;
if (!Yii::$app->user->isGuest) {
    $isFollowing = (new \yii\db\Query())
        ->from('{{%follow}}')
        ->where([
            'follower_id' => (int) Yii::$app->user->id,
            'artist_id' => (int) $model->id
        ])
        ->exists();
}
?>

<div class="artist-page">

    <section class="artist-hero">
        <div class="artist-avatar-big">
            <img src="<?= Html::encode($avatarUrl) ?>" alt="Artist photo <?= Html::encode($model->stage_name) ?>">
        </div>

        <div class="artist-main">
            <span class="artist-label">ARTIST</span>

            <h1 class="artist-name">
                <?= Html::encode($model->stage_name) ?>
            </h1>

            <div class="artist-meta-row">
                <?php if ($user): ?>
                    <span class="artist-meta-item">
                        Linked to <?= Html::encode($user->username ?? $user->email) ?>
                    </span>
                    <span class="artist-dot">•</span>
                <?php endif; ?>

                <span class="artist-meta-item">
                    On PHONYX since <?= Yii::$app->formatter->asDate($model->created_at) ?>
                </span>
            </div>

            <?php if (!empty($model->bio)): ?>
                <p class="artist-bio">
                    <?= nl2br(Html::encode($model->bio)) ?>
                </p>
            <?php else: ?>
                <p class="artist-bio artist-bio-empty">
                    This artist hasn't written a bio yet.
                </p>
            <?php endif; ?>

            <div class="artist-actions-row">

                <button type="button" class="btn btn-accent artist-btn-main" id="artist-play-top">
                    ▶ Play top tracks
                </button>

                <!-- Follow / unfollow  -->
                <button type="button"
                    class="btn btn-ghost artist-btn-secondary <?= $isFollowing ? 'is-following' : '' ?>"
                    id="artist-follow-btn" data-artist-id="<?= (int) $model->id ?>"
                    data-following="<?= $isFollowing ? '1' : '0' ?>"
                    data-follow-url="<?= Url::to(['artist/follow', 'id' => $model->id]) ?>"
                    data-unfollow-url="<?= Url::to(['artist/unfollow', 'id' => $model->id]) ?>">
                    <span class="artist-heart"><?= $isFollowing ? '♥' : '♡' ?></span>
                    <span class="artist-follow-label"><?= $isFollowing ? 'Following' : 'Follow' ?></span>
                </button>
            </div>
        </div>
    </section>

    <section class="artist-section">
        <header class="artist-section-header">
            <h2>Top tracks</h2>
            <p class="artist-section-subtitle">
                Latest or strongest tracks from this artist.
            </p>
        </header>

        <?php $tracks = $model->tracks ?? []; ?>

        <?php if (empty($tracks)): ?>
            <p class="artist-empty">
                There are no tracks linked to this artist yet.
            </p>
        <?php else: ?>
            <div class="artist-tracks-list">
                <?php foreach ($tracks as $track): ?>
                    <?php

                    $coverUrl = Yii::getAlias('@web') . '/img/default-cover.png';


                    $audioUrl = null;
                    if ($track->audioAsset && !empty($track->audioAsset->path)) {
                        $p = (string) $track->audioAsset->path;
                        $audioUrl = preg_match('~^https?://~i', $p)
                            ? $p
                            : (Yii::getAlias('@web') . '/' . ltrim($p, '/'));
                    }

                    $trackUrl = Url::to(['track/view', 'id' => $track->id]);

                    $durationLabel = method_exists($track, 'getDurationLabel')
                        ? $track->durationLabel
                        : ($track->duration ?? '');
                    ?>

                    <div class="artist-track-row">
                        <div class="artist-track-main">
                            <a href="<?= $trackUrl ?>" class="artist-track-cover">
                                <img src="<?= Html::encode($coverUrl) ?>" alt="">
                            </a>

                            <div class="artist-track-text">
                                <a href="<?= $trackUrl ?>" class="artist-track-title-link">
                                    <span class="artist-track-title">
                                        <?= Html::encode($track->title ?? 'Untitled') ?>
                                    </span>
                                </a>
                                <span class="artist-track-meta">
                                    <?= Html::encode($durationLabel ?: '–:–') ?>
                                </span>
                            </div>
                        </div>

                        <div class="artist-track-actions">
                            <?php if ($audioUrl): ?>
                                <button type="button" class="artist-track-play-btn" data-id="<?= (int) $track->id ?>"
                                    data-audio="<?= Html::encode($audioUrl) ?>"
                                    data-title="<?= Html::encode($track->title ?? '') ?>"
                                    data-artist="<?= Html::encode($model->stage_name) ?>"
                                    data-cover="<?= Html::encode($coverUrl) ?>">
                                    ▶
                                </button>
                            <?php else: ?>
                                <span class="artist-track-no-audio">
                                    No audio
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="artist-section">
        <header class="artist-section-header">
            <h2>Albums</h2>
            <p class="artist-section-subtitle">
            </p>
        </header>

        <?php $albums = $model->albums ?? []; ?>

        <?php if (empty($albums)): ?>
            <p class="artist-empty">
                No albums for this artist yet.
            </p>
        <?php else: ?>
            <div class="artist-albums-grid">
                <?php foreach ($albums as $album): ?>
                    <?php

                    $albumCover = $resolveAssetUrl($album->coverAsset ?? null, 'img/default-cover.png');
                    $albumUrl = Url::to(['album/view', 'id' => $album->id]);
                    ?>

                    <a href="<?= $albumUrl ?>" class="artist-album-card">
                        <div class="artist-album-cover">
                            <img src="<?= Html::encode($albumCover) ?>" alt="<?= Html::encode($album->title ?: 'Album') ?>">
                        </div>

                        <div class="artist-album-text">
                            <span class="artist-album-title">
                                <?= Html::encode($album->title ?: 'Sem título') ?>
                            </span>
                            <span class="artist-album-meta">
                                <?= Yii::$app->formatter->asDate($album->created_at) ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <!-- PLAYLISTS -->
    <section class="artist-section">
        <header class="artist-section-header">
            <h2>Playlists</h2>
            <p class="artist-section-subtitle">Public playlists by this profile.</p>
        </header>

        <?php if (empty($playlists)): ?>
            <p class="artist-empty">No playlists yet.</p>
        <?php else: ?>
            <div class="artist-albums-grid">
                <?php foreach ($playlists as $pl): ?>
                    <?php
                    
                    $plCover = null;
                    if (property_exists($pl, 'coverAsset') || method_exists($pl, 'getCoverAsset')) {
                        $plCover = $resolveAssetUrl($pl->coverAsset ?? null, 'img/default-cover.png');
                    } else {
                        $plCover = Yii::getAlias('@web') . '/img/default-cover.png';
                    }

                    $plUrl = Url::to(['playlist/view', 'id' => $pl->id]);
                    ?>
                    <a href="<?= $plUrl ?>" class="artist-album-card">
                        <div class="artist-album-cover">
                            <img src="<?= Html::encode($plCover) ?>" alt="<?= Html::encode($pl->title ?: 'Playlist') ?>">
                        </div>

                        <div class="artist-album-text">
                            <span class="artist-album-title"><?= Html::encode($pl->title ?: 'Untitled') ?></span>
                            <span class="artist-album-meta">Playlist</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- COMMUNITY -->
<section class="artist-section">
    <div class="sp-rowhead">
        <h2 class="sp-rowtitle">Seguidores</h2>

        <?php if (!empty($followers)): ?>
            <a class="sp-rowlink" href="<?= Url::to(['artist/followers', 'id' => $model->id]) ?>">
                Mostrar tudo
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($followers)): ?>
        <p class="artist-empty">No followers yet.</p>
    <?php else: ?>
        <div class="sp-cards">
            <?php foreach (array_slice($followers, 0, 10) as $u): ?>
                <?php
                    $defaultAvatar = Url::to('@web/img/default-avatar.png');
                    $uAvatarPath = $u->profileAsset->path ?? null;
                    $uAvatar = $uAvatarPath
                        ? (Yii::getAlias('@web') . '/' . ltrim($uAvatarPath, '/'))
                        : $defaultAvatar;

                    $uUrl = Url::to(['profile/view', 'id' => $u->id]);
                ?>

                <a class="sp-card" href="<?= $uUrl ?>">
                    <div class="sp-card__avatar">
                        <img src="<?= Html::encode($uAvatar) ?>"
                             onerror="this.src='<?= Html::encode($defaultAvatar) ?>'"
                             alt="">
                    </div>

                    <div class="sp-card__name"><?= Html::encode($u->username) ?></div>
                    <div class="sp-card__meta">Perfil</div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


<section class="artist-section">
    <div class="sp-rowhead">
        <h2 class="sp-rowtitle">A seguir</h2>

        <?php if (!empty($followingArtists)): ?>
            <a class="sp-rowlink" href="<?= Url::to(['artist/following', 'id' => $model->id]) ?>">
                Mostrar tudo
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($followingArtists)): ?>
        <p class="artist-empty">Not following any artists yet.</p>
    <?php else: ?>
        <div class="sp-cards">
            <?php foreach (array_slice($followingArtists, 0, 10) as $a): ?>
                <?php
                    $aAvatar = $resolveAssetUrl($a->avatarAsset ?? null, 'img/default-avatar.png');
                    $aUrl = Url::to(['artist/view', 'id' => $a->id]);
                ?>

                <a class="sp-card" href="<?= $aUrl ?>">
                    <div class="sp-card__avatar">
                        <img src="<?= Html::encode($aAvatar) ?>" alt="">
                    </div>

                    <div class="sp-card__name"><?= Html::encode($a->stage_name ?: 'Artist') ?></div>
                    <div class="sp-card__meta">Artista</div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


</div>
<?php
$this->registerJs(<<<JS
(function () {
  var followBtn = document.getElementById('artist-follow-btn');
  if (!followBtn) return;

  function getCsrf() {
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var paramMeta = document.querySelector('meta[name="csrf-param"]');
    return {
      token: tokenMeta ? tokenMeta.getAttribute('content') : '',
      param: paramMeta ? paramMeta.getAttribute('content') : '_csrf'
    };
  }

  followBtn.addEventListener('click', async function () {
    var isFollowing = this.dataset.following === '1';
    var url = isFollowing ? this.dataset.unfollowUrl : this.dataset.followUrl;

    var csrf = getCsrf();
    var body = new URLSearchParams();
    body.append(csrf.param, csrf.token);

    try {
      var res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body.toString()
      });

      var data = await res.json().catch(function(){ return null; });

      if (!res.ok || !data || !data.ok) {
        console.log('Follow failed', res.status, data);
        return;
      }

      this.dataset.following = data.following ? '1' : '0';
      this.classList.toggle('is-following', !!data.following);

      var heart = this.querySelector('.artist-heart');
      var label = this.querySelector('.artist-follow-label');
      if (heart) heart.textContent = data.following ? '♥' : '♡';
      if (label) label.textContent = data.following ? 'Following' : 'Follow';

    } catch (err) {
      console.error(err);
    }
  });
})();
JS, \yii\web\View::POS_END);
?>