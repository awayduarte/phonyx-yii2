<?php

namespace common\models;

use Yii;

class TrackFeaturedArtist extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'track_featured_artist';
    }

    public function rules()
    {
        return [
            [['track_id', 'artist_id'], 'required'],
            [['track_id', 'artist_id'], 'integer'],
            [['track_id', 'artist_id'], 'unique', 'targetAttribute' => ['track_id', 'artist_id']],

            // fk -> track
            [
                ['track_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Track::class, 'targetAttribute' => ['track_id' => 'id']
            ],

            // fk -> artist
            [
                ['artist_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Artist::class, 'targetAttribute' => ['artist_id' => 'id']
            ],
        ];
    }

    // pivot -> track
    public function getTrack()
    {
        return $this->hasOne(Track::class, ['id' => 'track_id']);
    }

    // pivot -> artist
    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['id' => 'artist_id']);
    }
}



