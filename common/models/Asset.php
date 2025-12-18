<?php

namespace common\models;

use Yii;
use yii\web\UploadedFile;

class Asset extends \yii\db\ActiveRecord
{
    // asset types
    const TYPE_IMAGE = 'image';
    const TYPE_AUDIO = 'audio';
    const TYPE_OTHER = 'other';

    // uploaded file (not stored in db)
    public $file;

    public static function tableName()
    {
        return 'asset';
    }

    public function rules()
    {
        return [
            // type is required
            [['type'], 'required'],

            // type validation
            [['type'], 'string'],
            ['type', 'in', 'range' => array_keys(self::optsType())],

            // file upload validation
            [
                ['file'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => ['jpg', 'jpeg', 'png', 'mp3',],
                'maxSize' => 1024 * 1024 * 20,
            ],

            // timestamps handled by db
            [['created_at', 'updated_at'], 'safe'],

            // path stored internally
            [['path'], 'string', 'max' => 255],
        ];
    }

    /**
     * before save logic
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // handle file upload
        if ($this->file instanceof UploadedFile) {

            // generate unique filename
            $filename = uniqid() . '.' . $this->file->extension;

            // define upload folder by type
            $folder = 'uploads/' . $this->type;

            // ensure folder exists
            if (!is_dir(Yii::getAlias('@webroot/' . $folder))) {
                mkdir(Yii::getAlias('@webroot/' . $folder), 0775, true);
            }

            // save file
            $this->file->saveAs(Yii::getAlias("@webroot/{$folder}/{$filename}"));

            // save relative path in db
            $this->path = "{$folder}/{$filename}";
        }
        return true;
    }

    // -----------------------
    // relations
    // -----------------------

    // asset -> albums (covers)
    public function getAlbums()
    {
        return $this->hasMany(Album::class, ['cover_asset_id' => 'id']);
    }

    // asset -> artists (avatars)
    public function getArtists()
    {
        return $this->hasMany(Artist::class, ['avatar_asset_id' => 'id']);
    }

    // asset -> playlists (covers)
    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['cover_asset_id' => 'id']);
    }

    // asset -> tracks (audio)
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['audio_asset_id' => 'id']);
    }

    // asset -> users (profile images)
    public function getUsers()
    {
        return $this->hasMany(User::class, ['profile_asset_id' => 'id']);
    }

    // -----------------------
    // helpers
    // -----------------------

    public static function optsType()
    {
        return [
            self::TYPE_IMAGE => 'image',
            self::TYPE_AUDIO => 'audio',
            self::TYPE_OTHER => 'other',
        ];
    }

    public function displayType()
    {
        return self::optsType()[$this->type] ?? null;
    }

    public function isTypeImage()
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isTypeAudio()
    {
        return $this->type === self::TYPE_AUDIO;
    }

    public function isTypeOther()
    {
        return $this->type === self::TYPE_OTHER;
    }
}
