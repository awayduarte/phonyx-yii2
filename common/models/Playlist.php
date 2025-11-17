<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "playlists".
 *
 * @property int $id
 * @property int $owner_user_id
 * @property string $title
 * @property int $is_public
 * @property int|null $cover_asset_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 * @property Asset|null $coverAsset
 * @property PlaylistTrack[] $playlistTracks
 * @property Track[] $tracks
 */
class Playlist extends ActiveRecord
{
    public static function tableName()
    {
        return 'playlists';
    }

    public function rules()
    {
        return [
            [['owner_user_id', 'title'], 'required'],
            [['owner_user_id', 'is_public', 'cover_asset_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 160],
            [
                ['owner_user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['owner_user_id' => 'id']
            ],
            [
                ['cover_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['cover_asset_id' => 'id']
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_user_id' => 'Owner User ID',
            'title' => 'Title',
            'is_public' => 'Is Public',
            'cover_asset_id' => 'Cover Asset ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /** O utilizador proprietário da playlist */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'owner_user_id']);
    }

    /** O asset usado como capa (se existir) */
    public function getCoverAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'cover_asset_id']);
    }

    /** Relação pivot playlist_tracks */
    public function getPlaylistTracks()
    {
        return $this->hasMany(PlaylistTrack::class, ['playlist_id' => 'id']);
    }

    /** Tracks que fazem parte desta playlist */
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['id' => 'track_id'])
            ->via('playlistTracks');
    }
}
