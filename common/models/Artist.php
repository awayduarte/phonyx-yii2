<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class Artist extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'artist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // required fields
            [['user_id', 'stage_name'], 'required'],

            // types
            [['user_id', 'avatar_asset_id'], 'integer'],
            [['bio'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],

            // strings
            [['stage_name'], 'string', 'max' => 150],

            // fk -> user
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],

            // one artist profile per user
            [
                'user_id',
                'unique',
                'message' => 'This user already has an artist profile.',
            ],

            // fk -> asset (avatar)
            [
                ['avatar_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['avatar_asset_id' => 'id'],
            ],
        ];
    }

    // -----------------------
    // Soft delete
    // -----------------------

    public function softDelete()
    {
        $this->deleted_at = date('Y-m-d H:i:s');
        return $this->save(false, ['deleted_at']);
    }

    public function restore()
    {
        $this->deleted_at = null;
        return $this->save(false, ['deleted_at']);
    }

    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }

    // -----------------------
    // Relations
    // -----------------------

    // artist -> owner user
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    // artist -> albums
    public function getAlbums()
    {
        return $this->hasMany(Album::class, ['artist_id' => 'id']);
    }

    // artist -> avatar image
    public function getAvatarAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'avatar_asset_id']);
    }

    // artist -> followers
    public function getFollowers()
    {
        return $this->hasMany(User::class, ['id' => 'follower_id'])
            ->viaTable('follow', ['artist_id' => 'id']);
    }

    // artist -> follow rows
    public function getFollows()
    {
        return $this->hasMany(Follow::class, ['artist_id' => 'id']);
    }

    // artist -> tracks
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['artist_id' => 'id']);
    }
}
