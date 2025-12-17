<?php

namespace common\models;

use Yii;

class Album extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'album';
    }

    public function rules()
    {
        return [
            [['artist_id', 'title'], 'required'],
            [['artist_id', 'cover_asset_id'], 'integer'],
            [['release_date', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],

            // fk -> artist
            [
                ['artist_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Artist::class,
                'targetAttribute' => ['artist_id' => 'id']
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
    // album -> artist
    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['id' => 'artist_id']);
    }

    // album -> cover image
    public function getCoverAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'cover_asset_id']);
    }

    // album -> tracks
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['album_id' => 'id']);
    }
}
