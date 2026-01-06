<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class Asset extends ActiveRecord
{
    // asset types
    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_OTHER = 'other';

    /**
     * Uploaded file (virtual attribute, not stored in DB)
     * @var UploadedFile|null
     */
    public $file;

    /**
     * Helper virtual attribute (optional usage)
     * @var int|null
     */
    public $used_count;

    public static function tableName(): string
    {
        return 'asset';
    }

    public function rules(): array
    {
        return [
            // File required only on create scenario
            [['file'], 'required', 'on' => 'create'],

            // Validate file
            [['file'], 'file',
                'skipOnEmpty' => true,
                'extensions' => ['jpg', 'jpeg', 'png', 'mp3'],
                'maxSize' => 1024 * 1024 * 12, // 12MB
            ],

            // Columns
            [['type'], 'string'],
            [['path'], 'string', 'max' => 255],


            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    protected function detectType(): string
    {
        if (!$this->file instanceof UploadedFile) {
            return self::TYPE_OTHER;
        }

        $ext = strtolower((string)$this->file->extension);

        if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return self::TYPE_IMAGE;
        }

        if ($ext === 'mp3') {
            return self::TYPE_AUDIO;
        }

        return self::TYPE_OTHER;
    }

   
    protected static function hasColumn(string $name): bool
    {
        $schema = static::getTableSchema();
        return $schema && isset($schema->columns[$name]);
    }

  
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

       
        $now = date('Y-m-d H:i:s');
        if ($insert) {
            if (static::hasColumn('created_at') && empty($this->created_at)) {
                $this->created_at = $now;
            }
        }
        if (static::hasColumn('updated_at')) {
            $this->updated_at = $now;
        }

        if ($this->file instanceof UploadedFile) {

            // Detect type automatically
            $this->type = $this->detectType();

            
            if ($insert && static::hasColumn('created_by_user_id') && !Yii::$app->user->isGuest) {
                $this->created_by_user_id = (int)Yii::$app->user->id;
            }

            // Define upload folder
            $folder = 'uploads/' . $this->type;
            $basePath = Yii::getAlias('@webroot/' . $folder);

            // Ensure folder exists
            if (!is_dir($basePath)) {
                @mkdir($basePath, 0775, true);
            }

            // Generate unique filename
            $ext = strtolower((string)$this->file->extension);
            $filename = uniqid('asset_', true) . '.' . $ext;

            // Save file
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $filename;
            if (!$this->file->saveAs($fullPath)) {
                $this->addError('file', 'Failed to save uploaded file.');
                return false;
            }

            // Save relative path
            $this->path = $folder . '/' . $filename;
        }

        return true;
    }

    // -----------------------
    // relations
    // -----------------------

   
    public function getCreatedByUser()
    {
        if (!static::hasColumn('created_by_user_id')) {
            // Column not present in DB -> no relation
            return $this->hasOne(User::class, ['id' => 'id'])->where('1=0');
        }

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

    public function isImage(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isAudio(): bool
    {
        return $this->type === self::TYPE_AUDIO;
    }
}
