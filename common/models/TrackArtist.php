<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "track_artists".
 *
 * @property int $track_id
 * @property int $artist_id
 * @property string $role
 *
 * @property Track $track
 * @property Artist $artist
 */
class TrackArtist extends ActiveRecord
{
    public static function tableName()
    {
        return 'track_artists';
    }

    public function rules()
    {
        return [
            [['track_id', 'artist_id'], 'required'],
            [['track_id', 'artist_id'], 'integer'],
            [['role'], 'string', 'max' => 40],
            [
                ['track_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Track::class,
                'targetAttribute' => ['track_id' => 'id'],
            ],
            [
                ['artist_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Artist::class,
                'targetAttribute' => ['artist_id' => 'id'],
            ],
        ];
    }

    /** A música associada */
    public function getTrack()
    {
        return $this->hasOne(Track::class, ['id' => 'track_id']);
    }

    /** O artista associado */
    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['id' => 'artist_id']);
    }
}
