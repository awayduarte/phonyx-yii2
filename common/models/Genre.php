<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "genres".
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $created_at
 *
 * @property Track[] $tracks
 */
class Genre extends ActiveRecord
{
    public static function tableName()
    {
        return 'genres';
    }

    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['created_at'], 'safe'],
            [['name', 'slug'], 'string', 'max' => 80],
            [['name'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'created_at' => 'Created At',
        ];
    }

    /** Todas as tracks deste género */
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['genre_id' => 'id']);
    }
}
