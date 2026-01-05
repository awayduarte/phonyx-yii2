<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use getID3;


/**
 * Track model
 */
class Track extends ActiveRecord
{
    /** @var UploadedFile  */
    public $audioFile;

    /** @var UploadedFile  */
    public $coverFile;

    /** @var int[] */
    public $featuredArtistIds = [];

    public static function tableName()
    {
        return 'track';
    }

    public function rules()
    {
        return [
            // required fields
            [['artist_id', 'title'], 'required'],

            // integers 
            [['artist_id', 'album_id', 'audio_asset_id', 'cover_asset_id', 'duration', 'genre_id'], 'integer'],

            // strings
            [['title'], 'string', 'max' => 255],

            // timestamps
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],

            // uploads
            [
                ['audioFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => ['mp3', 'wav', 'ogg', 'm4a'],
            ],
            [
                ['coverFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
            ],

            // featured artists
            [
                ['featuredArtistIds'],
                'each',
                'rule' => ['integer'],
            ],

            // foreign keys 
            [
                ['album_id'],
                'exist',
                'skipOnEmpty' => true,
                'targetClass' => Album::class,
                'targetAttribute' => ['album_id' => 'id'],
            ],
            [
                ['genre_id'],
                'exist',
                'skipOnEmpty' => true,
                'targetClass' => Genre::class,
                'targetAttribute' => ['genre_id' => 'id'],
            ],
            [
                ['artist_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Artist::class,
                'targetAttribute' => ['artist_id' => 'id'],
            ],
            [
                ['audio_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['audio_asset_id' => 'id'],
            ],
            [
                ['cover_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['cover_asset_id' => 'id'],
            ],
            
        ];
    }


    /* ========================
     * Relations
     * ======================== */

    // track -> artist
    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['id' => 'artist_id']);
    }

    // track -> album
    public function getAlbum()
    {
        return $this->hasOne(Album::class, ['id' => 'album_id']);
    }

    // track -> genre
    public function getGenre()
    {
        return $this->hasOne(Genre::class, ['id' => 'genre_id']);
    }

    // track -> audio asset
    public function getAudioAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'audio_asset_id']);
    }

    // track -> likes
    public function getLikes()
    {
        return $this->hasMany(Like::class, ['track_id' => 'id']);
    }

    // track -> liked users
    public function getLikedByUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('like', ['track_id' => 'id']);
    }

    // track -> playlists pivot
    public function getPlaylistTracks()
    {
        return $this->hasMany(PlaylistTrack::class, ['track_id' => 'id']);
    }

    // track -> playlists
    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['id' => 'playlist_id'])
            ->via('playlistTracks');
    }

    // track -> featured artist pivots
    public function getTrackFeaturedArtists()
    {
        return $this->hasMany(TrackFeaturedArtist::class, ['track_id' => 'id']);
    }

    // track -> featured artists
    public function getFeaturedArtists()
    {
        return $this->hasMany(Artist::class, ['id' => 'artist_id'])
            ->via('trackFeaturedArtists');
    }

    /**
     * Calculate audio duration from asset file
     */
    protected function calculateDurationFromAsset(): ?int
    {
        if (!$this->audioAsset || !$this->audioAsset->path) {
            return null;
        }

        $filePath = Yii::getAlias('@webroot/' . $this->audioAsset->path);

        if (!file_exists($filePath)) {
            return null;
        }

        $getID3 = new getID3();
        $info = $getID3->analyze($filePath);

        if (!empty($info['playtime_seconds'])) {
            return (int) round($info['playtime_seconds']);
        }

        return null;
    }


    /* ========================
     * Before Save
     * ======================== */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // album_id invalid → set NULL
        if ($this->album_id && !Album::find()->where(['id' => $this->album_id])->exists()) {
            $this->album_id = null;
        }

        // genre_id invalid → set NULL
        if ($this->genre_id && !Genre::find()->where(['id' => $this->genre_id])->exists()) {
            $this->genre_id = null;
        }

        // calculate duration only if empty
        if ($this->audio_asset_id && empty($this->duration)) {
            $duration = $this->calculateDurationFromAsset();
            if ($duration !== null) {
                $this->duration = $duration;
            }
        }

        return true;
    }

    /* ========================
     * Soft delete
     * ======================== */

    public function softDelete(): bool
    {
        $this->deleted_at = date('Y-m-d H:i:s');
        return $this->save(false, ['deleted_at']);
    }

    public function restore(): bool
    {
        $this->deleted_at = null;
        return $this->save(false, ['deleted_at']);
    }

    /* ========================
     * Computed properties (getters)
     * ======================== */

    public function getCoverUrl(): string
    {
        $default = \yii\helpers\Url::to('@web/img/default-cover.png', true);

       
        if (!property_exists($this, 'cover_asset_id') || empty($this->cover_asset_id)) {
            return $default;
        }

        $asset = Asset::findOne((int) $this->cover_asset_id);
        if (!$asset || empty($asset->path)) {
            return $default;
        }

        $path = (string) $asset->path;

        
        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

       
        $path = ltrim($path, '/');

        return \yii\helpers\Url::to('@web/' . $path, true);
    }

    public function getAudioUrl(): ?string
    {
        if (!$this->audioAsset || empty($this->audioAsset->path)) {
            return null;
        }

        $path = (string) $this->audioAsset->path;

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        return \yii\helpers\Url::to('@web/' . $path, true);
    }

    public function getDurationLabel(): string
    {
        $seconds = (int) ($this->duration ?? 0);
        if ($seconds <= 0) {
            return '--:--';
        }

        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        return sprintf('%d:%02d', $m, $s);
    }

    public function getArtistLabel(): string
    {
        return isset($this->artist) && !empty($this->artist->stage_name) ? $this->artist->stage_name : 'Unknown artist';
    }

    public function getLikesCount(): int
    {
        
        return (int) $this->getLikes()->count();
    }

}
