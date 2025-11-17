<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "tracks".
 *
 * @property int $id
 * @property string $title
 * @property int|null $album_id
 * @property int $audio_asset_id
 * @property int|null $cover_asset_id
 * @property int|null $genre_id
 * @property int|null $duration_sec
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Album|null $album
 * @property Asset $audioAsset
 * @property Asset|null $coverAsset
 * @property Genre|null $genre
 * @property TrackArtist[] $trackArtists
 * @property Artist[] $artists
 * @property PlaylistTrack[] $playlistTracks
 * @property Playlist[] $playlists
 * @property UserLike[] $userLikes
 * @property User[] $likedByUsers
 */
class Track extends ActiveRecord
{
    public static function tableName()
    {
        return 'tracks';
    }

    public function rules()
    {
        return [
            [['title', 'audio_asset_id'], 'required'],
            [['album_id', 'audio_asset_id', 'cover_asset_id', 'genre_id', 'duration_sec'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 200],
            [
                ['album_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Album::class,
                'targetAttribute' => ['album_id' => 'id'],
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
            [
                ['genre_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Genre::class,
                'targetAttribute' => ['genre_id' => 'id'],
            ],
        ];
    }

    /** Álbum onde esta música pertence */
    public function getAlbum()
    {
        return $this->hasOne(Album::class, ['id' => 'album_id']);
    }

    /** Asset do áudio da música */
    public function getAudioAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'audio_asset_id']);
    }

    /** Capa específica da música (se existir) */
    public function getCoverAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'cover_asset_id']);
    }

    /** Género da música */
    public function getGenre()
    {
        return $this->hasOne(Genre::class, ['id' => 'genre_id']);
    }

    /** Pivot track_artists */
    public function getTrackArtists()
    {
        return $this->hasMany(TrackArtist::class, ['track_id' => 'id']);
    }

    /** Artistas desta track (via pivot) */
    public function getArtists()
    {
        return $this->hasMany(Artist::class, ['id' => 'artist_id'])
            ->via('trackArtists');
    }

    /** Pivot playlist_tracks */
    public function getPlaylistTracks()
    {
        return $this->hasMany(PlaylistTrack::class, ['track_id' => 'id']);
    }

    /** Playlists onde esta música aparece */
    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['id' => 'playlist_id'])
            ->via('playlistTracks');
    }

    /** Pivot user_likes */
    public function getUserLikes()
    {
        return $this->hasMany(UserLike::class, ['track_id' => 'id']);
    }

    /** Users que deram like */
    public function getLikedByUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->via('userLikes');
    }
}
