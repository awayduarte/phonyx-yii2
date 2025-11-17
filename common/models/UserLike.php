<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_likes".
 *
 * @property int $user_id
 * @property int $track_id
 * @property string|null $created_at
 *
 * @property User $user
 * @property Track $track
 */
class UserLike extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_likes';
    }

    public function rules()
    {
        return [
            [['user_id', 'track_id'], 'required'],
            [['user_id', 'track_id'], 'integer'],
            [['created_at'], 'safe'],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],
            [
                ['track_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Track::class,
                'targetAttribute' => ['track_id' => 'id'],
            ],
        ];
    }

    /** O utilizador que fez like */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /** A track que recebeu like */
    public function getTrack()
    {
        return $this->hasOne(Track::class, ['id' => 'track_id']);
    }
}
