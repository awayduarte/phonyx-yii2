<?php

namespace common\models;

use Yii;

class PlaylistTrack extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'playlist_track';
    }

    public function rules()
    {
        return [
            [['playlist_id', 'track_id'], 'required'],
            [['playlist_id', 'track_id', 'position'], 'integer'],
            [['position'], 'default', 'value' => 0],
            [['playlist_id', 'track_id'], 'unique', 'targetAttribute' => ['playlist_id', 'track_id']],

            // fk -> playlist
            [
                ['playlist_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Playlist::class, 'targetAttribute' => ['playlist_id' => 'id']
            ],

            // fk -> track
            [
                ['track_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Track::class, 'targetAttribute' => ['track_id' => 'id']
            ],
        ];
    }

    // pivot -> playlist
    public function getPlaylist()
    {
        return $this->hasOne(Playlist::class, ['id' => 'playlist_id']);
    }

    // pivot -> track
    public function getTrack()
    {
        return $this->hasOne(Track::class, ['id' => 'track_id']);
    }
}
