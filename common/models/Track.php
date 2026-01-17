<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use getID3;
use yii\helpers\Url;

/**
 * Track model
 */
class Track extends ActiveRecord
{
    /** @var UploadedFile  */
    public $audioFile;

    /** @var UploadedFile  */
    public $coverFile;

    /** @var int[]  */
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
            [['artist_id', 'album_id', 'audio_asset_id', 'duration', 'genre_id'], 'integer'],

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
            [['featuredArtistIds'], 'each', 'rule' => ['integer']],

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
        ];
    }

    /* ========================
     * Relations
     * ======================== */

    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['id' => 'artist_id']);
    }

    public function getAlbum()
    {
        return $this->hasOne(Album::class, ['id' => 'album_id']);
    }

    public function getGenre()
    {
        return $this->hasOne(Genre::class, ['id' => 'genre_id']);
    }

    public function getAudioAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'audio_asset_id']);
    }

    public function getLikes()
    {
        return $this->hasMany(Like::class, ['track_id' => 'id']);
    }

    public function getLikedByUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('like', ['track_id' => 'id']);
    }

    public function getPlaylistTracks()
    {
        return $this->hasMany(PlaylistTrack::class, ['track_id' => 'id']);
    }

    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['id' => 'playlist_id'])
            ->via('playlistTracks');
    }

    public function getTrackFeaturedArtists()
    {
        return $this->hasMany(TrackFeaturedArtist::class, ['track_id' => 'id']);
    }

    public function getFeaturedArtists()
    {
        return $this->hasMany(Artist::class, ['id' => 'artist_id'])
            ->via('trackFeaturedArtists');
    }

    
    protected function calculateDurationFromAsset(): ?int
    {
        if (!$this->audioAsset || empty($this->audioAsset->path)) {
            return null;
        }

        $filePath = Yii::getAlias('@webroot/' . ltrim($this->audioAsset->path, '/'));

        if (!file_exists($filePath)) {
            return null;
        }

        $getID3 = new getID3();
        $info   = $getID3->analyze($filePath);

        return !empty($info['playtime_seconds'])
            ? (int) round($info['playtime_seconds'])
            : null;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        
        if ($this->album_id && !Album::find()->where(['id' => $this->album_id])->exists()) {
            $this->album_id = null;
        }

        
        if ($this->genre_id && !Genre::find()->where(['id' => $this->genre_id])->exists()) {
            $this->genre_id = null;
        }

        
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
     * getters
     * ======================== */

    public function getCoverUrl(): string
    {
        $default = Url::to('@web/img/default-cover.png', true);

        
        if ($this->album && property_exists($this->album, 'cover_asset_id') && !empty($this->album->cover_asset_id)) {
            $asset = Asset::findOne((int)$this->album->cover_asset_id);
            if ($asset && !empty($asset->path)) {
                return Url::to('@web/' . ltrim($asset->path, '/'), true);
            }
        }

        return $default;
    }

    public function getAudioUrl(): ?string
    {
        if (!$this->audioAsset || empty($this->audioAsset->path)) {
            return null;
        }

        $path = (string)$this->audioAsset->path;

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        return Url::to('@web/' . ltrim($path, '/'), true);
    }

    public function getDurationLabel(): string
    {
        $seconds = (int)($this->duration ?? 0);
        if ($seconds <= 0) {
            return '--:--';
        }

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    public function getArtistLabel(): string
    {
        return ($this->artist && isset($this->artist->stage_name)) ? $this->artist->stage_name : 'Unknown artist';
    }

    public function getLikesCount(): int
    {
        return (int)$this->getLikes()->count();
    }


    public function fields()
{
    $fields = parent::fields();

    $fields['audio_url'] = function() {
        return $this->audioUrl;
    };

    $fields['cover_url'] = function() {
        return $this->coverUrl;
    };

    return $fields;
}


    public function afterSave($insert, $changedAttributes)
{
    parent::afterSave($insert, $changedAttributes);

    $obj = new \stdClass();
    $obj->id = $this->id;
    $obj->title = $this->title;
    $obj->artist_id = $this->artist_id;
    $obj->duration = $this->duration;

    $json = json_encode($obj);

    if ($insert)
        $this->FazPublishNoMosquitto("TRACK_INSERT", $json);
    else
        $this->FazPublishNoMosquitto("TRACK_UPDATE", $json);
}

public function afterDelete()
{
    parent::afterDelete();

    $obj = new \stdClass();
    $obj->id = $this->id;

    $json = json_encode($obj);

    $this->FazPublishNoMosquitto("TRACK_DELETE", $json);
}

public function FazPublishNoMosquitto($topic, $msg)
{//"172.22.21.227"
    $server = "127.0.0.1";
    $port = 1883;
    $client_id = "phpMQTT-publisher";

    $mqtt = new \app\mosquitto\phpMQTT($server, $port, $client_id);

    if ($mqtt->connect(true, NULL, "", "")) {
        $mqtt->publish($topic, $msg, 0);
        $mqtt->close();
    } else {
        file_put_contents("mqtt_error.log", "MQTT connection timeout");
    }
}

}
