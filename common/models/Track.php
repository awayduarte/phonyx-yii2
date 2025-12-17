<?php

namespace common\models;

use Yii;

class Track extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'track';
    }

    public function rules()
    {
        return [
            [['artist_id', 'title', 'duration'], 'required'],
            [['artist_id', 'album_id', 'audio_asset_id', 'duration', 'genre_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],

            [
                ['artist_id'],
                'exist',
                'targetClass' => Artist::class,
                'targetAttribute' => ['artist_id' => 'id']
            ],

            [
                ['album_id'],
                'exist',
                'targetClass' => Album::class,
                'targetAttribute' => ['album_id' => 'id']
            ],

            [
                ['genre_id'],
                'exist',
                'targetClass' => Genre::class,
                'targetAttribute' => ['genre_id' => 'id']
            ],

            // optional audio file
            [
                ['audio_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['audio_asset_id' => 'id']
            ],
        ];
    }

    // track -> album
    public function getAlbum()
    {
        return $this->hasOne(Album::class, ['id' => 'album_id']);
    }

    // track -> artist
    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['id' => 'artist_id']);
    }

    // track -> audio file
    public function getAudioAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'audio_asset_id']);
    }

    // track -> genre
    public function getGenre()
    {
        return $this->hasOne(Genre::class, ['id' => 'genre_id']);
    }

    // track -> like rows
    public function getLikes()
    {
        return $this->hasMany(Like::class, ['track_id' => 'id']);
    }

    // track -> users who liked this track
    public function getLikedByUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('like', ['track_id' => 'id']);
    }

    // track -> pivot playlist rows
    public function getPlaylistTracks()
    {
        return $this->hasMany(PlaylistTrack::class, ['track_id' => 'id']);
    }

    // track -> playlists that contain this track
    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['id' => 'playlist_id'])
            ->viaTable('playlist_track', ['track_id' => 'id']);
    }
}
