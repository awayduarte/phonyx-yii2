<?php

namespace common\models;

use Yii;

class Like extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'like';
    }

    public function rules()
    {
        return [
            [['user_id', 'track_id'], 'required'],
            [['user_id', 'track_id'], 'integer'],
            [['created_at'], 'safe'],
            [['user_id', 'track_id'], 'unique', 'targetAttribute' => ['user_id', 'track_id']],

            // fk -> user
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],

            // fk -> track
            [
                ['track_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Track::class,
                'targetAttribute' => ['track_id' => 'id']
            ],
        ];
    }

    // like -> track
    public function getTrack()
    {
        return $this->hasOne(Track::class, ['id' => 'track_id']);
    }

    // like -> user
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function find()
    {
        return new LikeQuery(get_called_class());
    }


}
