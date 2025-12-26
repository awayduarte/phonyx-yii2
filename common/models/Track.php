<?php

namespace common\models;
use yii\web\UploadedFile;
use common\models\TrackFeaturedArtist;
use common\models\Artist;

use Yii;

class Track extends \yii\db\ActiveRecord

{
     /** @var UploadedFile */
     public $audioFile;

     /** @var UploadedFile */
     public $coverFile;
 
     /** @var array */
     public $featuredArtistIds = [];

    public static function tableName()
    
    {
        return 'track';
    }

    public function rules()
    {
        return [
            [['artist_id', 'title', 'genre_id'], 'required'],
            [['artist_id', 'album_id', 'audio_asset_id', 'duration', 'genre_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
    
            // uploads 
            [['audioFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'mp3,wav,ogg,m4a'],
            [['coverFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png,jpg,jpeg,webp'],
    
            // feats
            [['featuredArtistIds'], 'each', 'rule' => ['integer']],
    
            // exists FK
            [['artist_id'], 'exist', 'skipOnError' => true, 'targetClass' => Artist::class, 'targetAttribute' => ['artist_id' => 'id']],
            [['album_id'], 'exist', 'skipOnError' => true, 'targetClass' => Album::class, 'targetAttribute' => ['album_id' => 'id']],
            [['genre_id'], 'exist', 'skipOnError' => true, 'targetClass' => Genre::class, 'targetAttribute' => ['genre_id' => 'id']],
            [['audio_asset_id'], 'exist', 'skipOnError' => true, 'targetClass' => Asset::class, 'targetAttribute' => ['audio_asset_id' => 'id']],
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
    // Track -> pivot table rows (featured artists)
public function getTrackFeaturedArtists()
{
    return $this->hasMany(TrackFeaturedArtist::class, ['track_id' => 'id']);
}

// Track -> featured artists (many-to-many)
public function getFeaturedArtists()
{
    return $this->hasMany(Artist::class, ['id' => 'artist_id'])
        ->via('trackFeaturedArtists');
}
// Soft delete helpers
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


}
