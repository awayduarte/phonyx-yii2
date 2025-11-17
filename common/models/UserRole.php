<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_roles".
 *
 * @property int $user_id
 * @property int $role_id
 * @property string|null $created_at
 *
 * @property User $user
 * @property Role $role
 */
class UserRole extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_roles';
    }

    public function rules()
    {
        return [
            [['user_id', 'role_id'], 'required'],
            [['user_id', 'role_id'], 'integer'],
            [['created_at'], 'safe'],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],
            [
                ['role_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Role::class,
                'targetAttribute' => ['role_id' => 'id'],
            ],
        ];
    }

    /** O utilizador que possui este role */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /** O role atribuído ao utilizador */
    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }
}
