<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_follows".
 *
 * @property int $follower_user_id
 * @property int $artist_id
 * @property string|null $created_at
 *
 * @property Artists $artists
 * @property Users $users
 */
class UserFollow extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_follows';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['follower_user_id', 'artist_id'], 'required'],
            [['follower_user_id', 'artist_id'], 'integer'],
            [['created_at'], 'safe'],
            [['follower_user_id', 'artist_id'], 'unique', 'targetAttribute' => ['follower_user_id', 'artist_id']],
            [['artist_id'], 'exist', 'skipOnError' => true, 'targetClass' => Artists::className(), 'targetAttribute' => ['artist_id' => 'id']],
            [['follower_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['follower_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'follower_user_id' => 'Follower User ID',
            'artist_id' => 'Artist ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Artists]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArtists()
    {
        return $this->hasOne(Artists::className(), ['id' => 'artist_id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasOne(Users::className(), ['id' => 'follower_user_id']);
    }

}
