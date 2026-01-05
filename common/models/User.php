<?php

namespace common\models;

use Yii;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;


class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    // -----------------------
    // Status
    // -----------------------

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;

    // -----------------------
    // Roles
    // -----------------------

    const ROLE_ADMIN = 'admin';
    const ROLE_ARTIST = 'artist';
    const ROLE_USER = 'user';

    // -----------------------
    // Virtual attributes
    // -----------------------

    public $password;

    /** @var \yii\web\UploadedFile|null Profile image upload */
    public $profileFile;


    // -----------------------
    // Table
    // -----------------------

    public static function tableName()
    {
        return 'user';
    }

    // -----------------------
    // Rules
    // -----------------------

    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            ['password', 'required', 'on' => 'create'],

            [['status', 'profile_asset_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['role'], 'string'],

            [['username'], 'string', 'max' => 100],
            [['email'], 'string', 'max' => 150],
            [['password'], 'string', 'min' => 6],

            [['email'], 'unique'],
            ['role', 'in', 'range' => array_keys(self::optsRole())],

            [
                ['profile_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['profile_asset_id' => 'id'],
            ],
            // Profile image upload (optional)
            [
                ['profileFile'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
                'maxSize' => 5 * 1024 * 1024,
            ],

        ];
    }

    // -----------------------
    // IdentityInterface
    // -----------------------

    // Prevent inactive or soft-deleted users from restoring session
    public static function findIdentity($id)
    {
        return static::find()
            ->where(['id' => $id])
            ->andWhere(['status' => 10])
            ->andWhere(['deleted_at' => null])
            ->one();
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne([
            'access_token' => $token,
            'deleted_at' => null,
        ]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    // -----------------------
    // Login helpers
    // -----------------------

    public static function findByEmail($email)
    {
        return static::findOne([
            'email' => $email,
            'status' => 10,
            'deleted_at' => null,
        ]);
    }

    public static function findByUsernameOrEmail($identifier)
    {
        return static::find()
            ->where([
                'username' => $identifier,
                'status' => 10,
                'deleted_at' => null,
            ])
            ->orWhere([
                'email' => $identifier,
                'status' => 10,
                'deleted_at' => null,
            ])
            ->one();
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString(40);
    }

    // -----------------------
    // Password reset helpers
    // -----------------------

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
            'deleted_at' => null,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string|null $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'] ?? 3600;
        return $timestamp + $expire >= time();
    }

    // -----------------------
    // Auto handling before/after save
    // -----------------------

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!empty($this->password)) {
            $this->setPassword($this->password);
        }

        if ($insert) {
            $this->generateAuthKey();
        }

        return true;
    }

    // Ensure artist profile exists when role is artist
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // only act if role changed
        if (!array_key_exists('role', $changedAttributes)) {
            return;
        }

        // became artist
        if ($this->role === self::ROLE_ARTIST) {

            $artist = Artist::find()
                ->where(['user_id' => $this->id])
                ->one();

            if ($artist) {
                // restore soft deleted artist
                if ($artist->isDeleted()) {
                    $artist->restore();
                }
            } else {
                // create new artist profile
                $artist = new Artist();
                $artist->user_id = $this->id;
                $artist->stage_name = $this->username;
                $artist->save(false);
            }

            return;
        }

        // left artist role
        if ($this->artist && !$this->artist->isDeleted()) {
            $this->artist->softDelete();
        }
    }

    // -----------------------
    // Soft delete
    // -----------------------

    public function softDelete()
    {
        $this->deleted_at = date('Y-m-d H:i:s');
        return $this->save(false, ['deleted_at']);
    }

    public function restore()
    {
        $this->deleted_at = null;
        return $this->save(false, ['deleted_at']);
    }

    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }

    // -----------------------
    // Relations
    // -----------------------

    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['user_id' => 'id']);
    }

    public function getFollowedArtists()
    {
        return $this->hasMany(Artist::class, ['id' => 'artist_id'])
            ->viaTable('follow', ['follower_id' => 'id']);
    }

    public function getFollows()
    {
        return $this->hasMany(Follow::class, ['follower_id' => 'id']);
    }

    public function getLikes()
    {
        return $this->hasMany(Like::class, ['user_id' => 'id']);
    }

    public function getLikedTracks()
    {
        return $this->hasMany(Track::class, ['id' => 'track_id'])
            ->viaTable('like', ['user_id' => 'id']);
    }

    public function getPlaylists()
    {
        return $this->hasMany(Playlist::class, ['user_id' => 'id']);
    }

    public function getProfileAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'profile_asset_id']);
    }

    // -----------------------
    // Roles helpers
    // -----------------------

    public static function optsRole()
    {
        return [
            self::ROLE_ADMIN => 'admin',
            self::ROLE_ARTIST => 'artist',
            self::ROLE_USER => 'user',
        ];
    }

    public function displayRole()
    {
        return self::optsRole()[$this->role] ?? null;
    }
}
