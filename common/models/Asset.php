<?php

namespace common\models;

use Yii;

class Asset extends \yii\db\ActiveRecord
{
    // asset types
    const TYPE_IMAGE = 'image';
    const TYPE_AUDIO = 'audio';
    const TYPE_VIDEO = 'video';
    const TYPE_OTHER = 'other';

    public static function tableName()
    {
        return 'asset';
    }

    public function rules()
    {
        return [
            [['path', 'type'], 'required'],
            [['type'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['path'], 'string', 'max' => 255],
            ['type', 'in', 'range' => array_keys(self::optsType())],
        ];
    }

    // asset -> albums (cover images)
    public function getAlbums()
    {
        return $this->hasMany(Album::class, ['cover_asset_id' => 'id']);
    }

    // asset -> artists (avatars)
    public function getArtists()
    {
        return $this->hasMany(Artist::class, ['avatar_asset_id' => 'id']);
    }

    // asset -> playlists (covers)
    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['cover_asset_id' => 'id']);
    }

    // asset -> tracks (audio files)
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['audio_asset_id' => 'id']);
    }

    // asset -> users (profile pictures)
    public function getUsers()
    {
        return $this->hasMany(User::class, ['profile_asset_id' => 'id']);
    }

    // enum labels
    public static function optsType()
    {
        return [
            self::TYPE_IMAGE => 'image',
            self::TYPE_AUDIO => 'audio',
            self::TYPE_VIDEO => 'video',
            self::TYPE_OTHER => 'other',
        ];
    }

    public function displayType()
    {
        return self::optsType()[$this->type];
    }

    public function isTypeImage()
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function setTypeToImage()
    {
        $this->type = self::TYPE_IMAGE;
    }

    public function isTypeAudio()
    {
        return $this->type === self::TYPE_AUDIO;
    }

    public function setTypeToAudio()
    {
        $this->type = self::TYPE_AUDIO;
    }

    public function isTypeVideo()
    {
        return $this->type === self::TYPE_VIDEO;
    }

    public function setTypeToVideo()
    {
        $this->type = self::TYPE_VIDEO;
    }

    public function isTypeOther()
    {
        return $this->type === self::TYPE_OTHER;
    }

    public function setTypeToOther()
    {
        $this->type = self::TYPE_OTHER;
    }
}