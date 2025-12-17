<?php

namespace common\models;

use Yii;

class Follow extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'follow';
    }

    public function rules()
    {
        return [
            [['follower_id', 'artist_id'], 'required'],
            [['follower_id', 'artist_id'], 'integer'],
            [['created_at'], 'safe'],
            [['follower_id', 'artist_id'], 'unique', 'targetAttribute' => ['follower_id', 'artist_id']],

            // fk -> user (follower)
            [
                ['follower_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['follower_id' => 'id']
            ],

            // fk -> artist
            [
                ['artist_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Artist::class,
                'targetAttribute' => ['artist_id' => 'id']
            ],
        ];
    }

    // follow -> artist
    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['id' => 'artist_id']);
    }

    // follow -> follower user
    public function getFollower()
    {
        return $this->hasOne(User::class, ['id' => 'follower_id']);
    }

    public static function find()
    {
        return new FollowQuery(get_called_class());
    }
}