<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model for table "users".
 *
 * @property int $id
 * @property string $email
 * @property string $username
 * @property string $password_hash
 * @property string|null $display_name
 * @property int $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * Virtual attributes:
 * @property string $password_plain
 * @property string $role
 *
 * Relations:
 * @property UserRole[] $userRoles
 * @property Role[] $roles
 * @property Artist|null $artist
 * @property AuthToken[] $authTokens
 * @property UserFollow[] $userFollows
 * @property Artist[] $followedArtists
 * @property UserFollow[] $artistFollowers
 * @property UserLike[] $userLikes
 */
class User extends ActiveRecord implements IdentityInterface
{
    /** Virtual attributes (not DB columns) */
    public $password_plain;
    public $role;

    public static function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        return [
            [['email', 'username'], 'required'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['email', 'password_hash'], 'string', 'max' => 255],
            [['username', 'display_name'], 'string', 'max' => 120],

            [['email'], 'unique'],
            [['username'], 'unique'],

            // form fields
            [['password_plain', 'role'], 'safe'],

            // when creating a user, force password
            ['password_plain', 'required', 'when' => function ($model) {
                return $model->isNewRecord;
            }, 'whenClient' => "function(){ return $('#user-id').length === 0; }"],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'password_plain' => 'Password',
            'display_name' => 'Display Name',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'role' => 'Role',
        ];
    }

    // ============================================
    // RELATIONS
    // ============================================

    public function getUserRoles()
    {
        return $this->hasMany(UserRole::class, ['user_id' => 'id']);
    }

    public function getRoles()
    {
        return $this->hasMany(Role::class, ['id' => 'role_id'])
            ->via('userRoles');
    }

    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['user_id' => 'id']);
    }

    public function getAuthTokens()
    {
        return $this->hasMany(AuthToken::class, ['user_id' => 'id']);
    }

    public function getUserFollows()
    {
        return $this->hasMany(UserFollow::class, ['follower_user_id' => 'id']);
    }

    public function getFollowedArtists()
    {
        return $this->hasMany(Artist::class, ['id' => 'artist_id'])
            ->via('userFollows');
    }

    public function getArtistFollowers()
    {
        return $this->hasMany(UserFollow::class, ['artist_id' => 'id']);
    }

    public function getUserLikes()
    {
        return $this->hasMany(UserLike::class, ['user_id' => 'id']);
    }

    // ============================================
    // IDENTITY INTERFACE (login backend)
    // ============================================

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return AuthToken::findOne(['token_hash' => $token]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    // ============================================
    // PASSWORD + RBAC HANDLING
    // ============================================

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;

        if (!empty($this->password_plain)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password_plain);
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!empty($this->role)) {
            $auth = Yii::$app->authManager;

            // remove all roles first
            $auth->revokeAll($this->id);

            $roleObj = $auth->getRole($this->role);
            if ($roleObj) {
                $auth->assign($roleObj, $this->id);
            }
        }
    }
}
