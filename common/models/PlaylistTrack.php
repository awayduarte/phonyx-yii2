<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "playlist_tracks".
 *
 * @property int $playlist_id
 * @property int $track_id
 * @property int $position
 * @property int $added_by
 * @property string|null $added_at
 *
 * @property Playlist $playlist
 * @property Track $track
 * @property User $addedBy
 */
class PlaylistTrack extends ActiveRecord
{
    public static function tableName()
    {
        return 'playlist_tracks';
    }

    public function rules()
    {
        return [
            [['playlist_id', 'track_id', 'position', 'added_by'], 'required'],
            [['playlist_id', 'track_id', 'position', 'added_by'], 'integer'],
            [['added_at'], 'safe'],
            [
                ['playlist_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Playlist::class,
                'targetAttribute' => ['playlist_id' => 'id']
            ],
            [
                ['track_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Track::class,
                'targetAttribute' => ['track_id' => 'id']
            ],
            [
                ['added_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['added_by' => 'id']
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'playlist_id' => 'Playlist ID',
            'track_id' => 'Track ID',
            'position' => 'Position',
            'added_by' => 'Added By',
            'added_at' => 'Added At',
        ];
    }

    /** A playlist a que este registo pertence */
    public function getPlaylist()
    {
        return $this->hasOne(Playlist::class, ['id' => 'playlist_id']);
    }

    /** A track associada */
    public function getTrack()
    {
        return $this->hasOne(Track::class, ['id' => 'track_id']);
    }

    /** O utilizador que adicionou esta track à playlist */
    public function getAddedBy()
    {
        return $this->hasOne(User::class, ['id' => 'added_by']);
    }
}
