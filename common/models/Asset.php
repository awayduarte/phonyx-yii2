<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "assets".
 *
 * @property int $id
 * @property int $type
 * @property string $storage_path
 * @property string $mime_type
 * @property int|null $duration_sec
 * @property int|null $size_bytes
 * @property int $play_count
 * @property string|null $created_at
 *
 * @property Album[] $albums
 * @property Artist[] $artists
 * @property Playlist[] $playlists
 * @property Track[] $audioTracks
 * @property Track[] $coverTracks
 */
class Asset extends ActiveRecord
{
    public static function tableName()
    {
        return 'assets';
    }

    public function rules()
    {
        return [
            [['duration_sec', 'size_bytes'], 'default', 'value' => null],
            [['play_count'], 'default', 'value' => 0],
            [['type', 'storage_path', 'mime_type'], 'required'],
            [['type', 'duration_sec', 'size_bytes', 'play_count'], 'integer'],
            [['created_at'], 'safe'],
            [['storage_path'], 'string', 'max' => 500],
            [['mime_type'], 'string', 'max' => 120],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'storage_path' => 'Storage Path',
            'mime_type' => 'Mime Type',
            'duration_sec' => 'Duration Sec',
            'size_bytes' => 'Size Bytes',
            'play_count' => 'Play Count',
            'created_at' => 'Created At',
        ];
    }

    /** Álbuns cuja capa usa este asset */
    public function getAlbums()
    {
        return $this->hasMany(Album::class, ['cover_asset_id' => 'id']);
    }

    /** Artistas cuja foto de perfil usa este asset */
    public function getArtists()
    {
        return $this->hasMany(Artist::class, ['profile_asset_id' => 'id']);
    }

    /** Playlists cuja capa usa este asset */
    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['cover_asset_id' => 'id']);
    }

    /** Tracks que usam este asset como ÁUDIO */
    public function getAudioTracks()
    {
        return $this->hasMany(Track::class, ['audio_asset_id' => 'id']);
    }

    /** Tracks que usam este asset como CAPA */
    public function getCoverTracks()
    {
        return $this->hasMany(Track::class, ['cover_asset_id' => 'id']);
    }
}
