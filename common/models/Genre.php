<?php

namespace common\models;

use Yii;

class Genre extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'genre';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique'],
        ];
    }

    // genre -> tracks
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['genre_id' => 'id']);
    }
}