<?php

namespace common\models;

use Yii;

class Playlist extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'playlist';
    }

    public function rules()
    {
        return [
            [['user_id', 'title'], 'required'],
            [['user_id', 'cover_asset_id'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],

            // fk -> user
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],

            // fk -> cover asset
            [
                ['cover_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['cover_asset_id' => 'id']
            ],
        ];
    }

    // playlist -> cover image
    public function getCoverAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'cover_asset_id']);
    }

    // playlist -> pivot rows
    public function getPlaylistTracks()
    {
        return $this->hasMany(PlaylistTrack::class, ['playlist_id' => 'id']);
    }

    // playlist -> tracks
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['id' => 'track_id'])
            ->viaTable('playlist_track', ['playlist_id' => 'id']);
    }

    // playlist -> owner user
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
