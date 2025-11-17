<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;

/**
 * This is the model class for table "users".
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
 * RELATIONS
 * @property UserRole[] $userRoles
 * @property Role[] $roles
 * @property Artist|null $artist
 * @property AuthToken[] $authTokens
 * @property UserFollow[] $userFollows           // following others
 * @property Artist[] $followedArtists           // artists this user follows
 * @property UserFollow[] $artistFollowers       // users following this user (if artist)
 * @property UserLike[] $userLikes
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        return [
            [['email', 'username', 'password_hash'], 'required'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['email', 'password_hash'], 'string', 'max' => 255],
            [['username', 'display_name'], 'string', 'max' => 120],
            [['email'], 'unique'],
            [['username'], 'unique'],
        ];
    }

    // -----------------------------------------------------
    // RELAÇÕES
    // -----------------------------------------------------

    /** Relação pivot user_roles */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::class, ['user_id' => 'id']);
    }

    /** Os roles RBAC deste user */
    public function getRoles()
    {
        return $this->hasMany(Role::class, ['id' => 'role_id'])
            ->via('userRoles');
    }

    /** Caso o user seja um artista, devolve o Artist */
    public function getArtist()
    {
        return $this->hasOne(Artist::class, ['user_id' => 'id']);
    }

    /** Tokens de autenticação (login API) */
    public function getAuthTokens()
    {
        return $this->hasMany(AuthToken::class, ['user_id' => 'id']);
    }

    /** Users que ESTE user segue (user_follows pivot) */
    public function getUserFollows()
    {
        return $this->hasMany(UserFollow::class, ['follower_user_id' => 'id']);
    }

    /** Artistas que ESTE user segue */
    public function getFollowedArtists()
    {
        return $this->hasMany(Artist::class, ['id' => 'artist_id'])
            ->via('userFollows');
    }

    /** Users que seguem ESTE user (se ele for artista) */
    public function getArtistFollowers()
    {
        return $this->hasMany(UserFollow::class, ['artist_id' => 'id']);
    }

    /** Likes feitos pelo user */
    public function getUserLikes()
    {
        return $this->hasMany(UserLike::class, ['user_id' => 'id']);
    }

    // -----------------------------------------------------
    // IDENTITY INTERFACE (necessário p/ backend login)
    // -----------------------------------------------------

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /** Autenticação por token (API) */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return AuthToken::findOne(['token_hash' => $token]);
    }

    /** Login backend (por username) */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function getId()
    {
        return $this->id;
    }

    /** Não usas authKey, mas interface exige */
    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }

    /** Validação da password no login backend */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    // -----------------------------------------------------
    // FUNÇÃO ÚTIL (opcional) - SET PASSWORD
    // -----------------------------------------------------
    /** Define um password e gera o hash corretamente */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
}
