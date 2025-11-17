<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "albums".
 *
 * @property int $id
 * @property string $title
 * @property int $main_artist_id
 * @property int|null $cover_asset_id
 * @property string|null $release_date
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Artist $mainArtist
 * @property Asset $coverAsset
 * @property Track[] $tracks
 */
class Album extends ActiveRecord
{
    public static function tableName()
    {
        return 'albums';
    }

    public function rules()
    {
        return [
            [['cover_asset_id', 'release_date'], 'default', 'value' => null],
            [['title', 'main_artist_id'], 'required'],
            [['main_artist_id', 'cover_asset_id'], 'integer'],
            [['release_date', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 200],
            [
                ['cover_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['cover_asset_id' => 'id']
            ],
            [
                ['main_artist_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Artist::class,
                'targetAttribute' => ['main_artist_id' => 'id']
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'main_artist_id' => 'Main Artist',
            'cover_asset_id' => 'Cover Asset',
            'release_date' => 'Release Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    // ---------------------------------------
    // RELATIONS 
    // ---------------------------------------

    public function getMainArtist()
    {
        return $this->hasOne(Artist::class, ['id' => 'main_artist_id']);
    }

    public function getCoverAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'cover_asset_id']);
    }

    public function getTracks()
    {
        return $this->hasMany(Track::class, ['album_id' => 'id']);
    }
}
