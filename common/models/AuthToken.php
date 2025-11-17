<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_tokens".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token_hash
 * @property string|null $created_at
 * @property string $expires_at
 *
 * @property User $user
 */
class AuthToken extends ActiveRecord
{
    public static function tableName()
    {
        return 'auth_tokens';
    }

    public function rules()
    {
        return [
            [['user_id', 'token_hash', 'expires_at'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'expires_at'], 'safe'],
            [['token_hash'], 'string', 'max' => 128],
            [['token_hash'], 'unique'],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token_hash' => 'Token Hash',
            'created_at' => 'Created At',
            'expires_at' => 'Expires At',
        ];
    }

    /** utilizador dono deste token */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
