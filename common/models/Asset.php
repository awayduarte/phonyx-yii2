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

    public $used_count;

    public static function tableName()
    {
        return 'asset';
    }

    public function rules()
    {
        return [
            // file required only on create
            [['file'], 'required', 'on' => 'create'],

            // file validation
            [
                ['file'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['jpg', 'jpeg', 'png', 'mp3'],
                'maxSize' => 1024 * 1024 * 12, // 12mb max
            ],

            // detected asset type
            [['type'], 'string'],

            // stored relative path
            [['path'], 'string', 'max' => 255],

            // who uploaded the asset
            [['created_by_user_id'], 'integer'],

            // timestamps handled by db
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * detect asset type from file extension
     */
    protected function detectType()
    {
        $ext = strtolower($this->file->extension);

        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            return self::TYPE_IMAGE;
        }

        if ($ext === 'mp3') {
            return self::TYPE_AUDIO;
        }

        return self::TYPE_OTHER;
    }

    /**
     * before save logic
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->file instanceof UploadedFile) {

            // detect type automatically
            $this->type = $this->detectType();

            // set uploader user only on create
            if ($this->isNewRecord && !Yii::$app->user->isGuest) {
                $this->created_by_user_id = Yii::$app->user->id;
            }

            // define upload folder
            $folder = 'uploads/' . $this->type;
            $basePath = Yii::getAlias('@webroot/' . $folder);

            // ensure folder exists
            if (!is_dir($basePath)) {
                mkdir($basePath, 0775, true);
            }

            // generate unique filename
            $filename = uniqid() . '.' . $this->file->extension;

            // save file
            $this->file->saveAs($basePath . '/' . $filename);

            // save relative path
            $this->path = $folder . '/' . $filename;
        }

        return true;
    }

    // -----------------------
    // relations
    // -----------------------

    // who uploaded this asset
    public function getCreatedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by_user_id']);
    }

    public function getAlbums()
    {
        return $this->hasMany(Album::class, ['cover_asset_id' => 'id']);
    }

    public function getArtists()
    {
        return $this->hasMany(Artist::class, ['avatar_asset_id' => 'id']);
    }

    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['cover_asset_id' => 'id']);
    }

    public function getTracks()
    {
        return $this->hasMany(Track::class, ['audio_asset_id' => 'id']);
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['profile_asset_id' => 'id']);
    }

    // -----------------------
    // helpers
    // -----------------------

    public function isImage()
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isAudio()
    {
        return $this->type === self::TYPE_AUDIO;
    }
}
