<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "roles".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 *
 * @property UserRole[] $userRoles
 */
class Role extends ActiveRecord
{
    public static function tableName()
    {
        return 'roles';
    }

    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 60],
            [['code'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'description' => 'Description',
        ];
    }

    /** Relação com user_roles */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::class, ['role_id' => 'id']);
    }
}
