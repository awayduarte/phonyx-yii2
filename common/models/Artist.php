<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "artists".
 *
 * @property int $id
 * @property int $user_id
 * @property string $stage_name
 * @property string|null $bio
 * @property int|null $profile_asset_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Album[] $albums
 * @property Asset $profileAsset
 * @property User $user
 * @property User[] $followers
 * @property UserFollow[] $userFollows
 * @property TrackArtist[] $trackArtists
 */
class Artist extends ActiveRecord
{
    public static function tableName()
    {
        return 'artists';
    }

    public function rules()
    {
        return [
            [['bio', 'profile_asset_id'], 'default', 'value' => null],
            [['user_id', 'stage_name'], 'required'],
            [['user_id', 'profile_asset_id'], 'integer'],
            [['bio'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['stage_name'], 'string', 'max' => 120],
            [['user_id'], 'unique'],
            [
                ['profile_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['profile_asset_id' => 'id']
            ],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
        ];
    }

    // ---------------------------------------
    // RELATIONS
    // ---------------------------------------

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'stage_name' => 'Stage Name',
            'bio' => 'Bio',
            'profile_asset_id' => 'Profile Asset ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /** Uma lista de álbuns criados por este artista */
    public function getAlbums()
    {
        return $this->hasMany(Album::class, ['main_artist_id' => 'id']);
    }

    /** O asset que representa a foto/perfil deste artista */
    public function getProfileAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'profile_asset_id']);
    }

    /** O utilizador associado a este artista */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /** Lista de users que seguem este artista */
    public function getFollowers()
    {
        return $this->hasMany(User::class, ['id' => 'follower_user_id'])
            ->viaTable('user_follows', ['artist_id' => 'id']);
    }

    /** Relação direta com a pivot user_follows */
    public function getUserFollows()
    {
        return $this->hasMany(UserFollow::class, ['artist_id' => 'id']);
    }

    /** Relação direta com a pivot track_artists */
    public function getTrackArtists()
    {
        return $this->hasMany(TrackArtist::class, ['artist_id' => 'id']);
    }
}
