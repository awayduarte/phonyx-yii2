<?php

namespace common\models;

use Yii;

class Playlist extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'playlist';
    }

    public function rules()
    {
        return [
            [['user_id', 'title'], 'required'],
            [['user_id', 'cover_asset_id'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],

            // fk -> user
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],

            // fk -> cover asset
            [
                ['cover_asset_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Asset::class,
                'targetAttribute' => ['cover_asset_id' => 'id']
            ],
        ];
    }

    // playlist -> cover image
    public function getCoverAsset()
    {
        return $this->hasOne(Asset::class, ['id' => 'cover_asset_id']);
    }

    // playlist -> pivot rows
    public function getPlaylistTracks()
    {
        return $this->hasMany(PlaylistTrack::class, ['playlist_id' => 'id']);
    }

    // playlist -> tracks
    public function getTracks()
    {
        return $this->hasMany(Track::class, ['id' => 'track_id'])
            ->viaTable('playlist_track', ['playlist_id' => 'id']);
    }

    // playlist
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
    
    public function getLikedByUsers()
    {
        return $this->hasMany(\common\models\User::class, ['id' => 'user_id'])
            ->viaTable('{{%playlist_like}}', ['playlist_id' => 'id']);
    }

   
    public function getLikes()
    {
        return $this->hasMany(\yii\db\ActiveRecord::class, ['playlist_id' => 'id'])
            ->from('{{%playlist_like}}');
    }

    // Helper
    public function isLikedBy(int $userId): bool
    {
        return (new \yii\db\Query())
            ->from('{{%playlist_like}}')
            ->where(['playlist_id' => (int) $this->id, 'user_id' => (int) $userId])
            ->exists();
    }


    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $obj = new \stdClass();
        $obj->id = $this->id;
        $obj->title = $this->title;
        $obj->user_id = $this->user_id;

        $json = json_encode($obj);

        if ($insert)
            $this->FazPublishNoMosquitto("PLAYLIST_INSERT", $json);
        else
            $this->FazPublishNoMosquitto("PLAYLIST_UPDATE", $json);
    }


    public function afterDelete()
    {
        parent::afterDelete();

        $obj = new \stdClass();
        $obj->id = $this->id;

        $json = json_encode($obj);

        $this->FazPublishNoMosquitto("PLAYLIST_DELETE", $json);
    }

        public function FazPublishNoMosquitto($topic, $msg)
    {
        $server = "172.22.21.227";
        $port = 1883;
        $client_id = "phpMQTT-playlist";

        $mqtt = new \app\mosquitto\phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, "", "")) {
            $mqtt->publish($topic, $msg, 0);
            $mqtt->close();
        } else {
            file_put_contents("mqtt_error.log", "MQTT connection timeout");
        }
    }

    public function notifyAddTrack($trackId)
    {
        $obj = new \stdClass();
        $obj->playlist_id = $this->id;
        $obj->track_id = $trackId;

        $this->FazPublishNoMosquitto("PLAYLIST_ADD_TRACK", json_encode($obj));
    }

    public function notifyRemoveTrack($trackId)
    {
        $obj = new \stdClass();
        $obj->playlist_id = $this->id;
        $obj->track_id = $trackId;

        $this->FazPublishNoMosquitto("PLAYLIST_REMOVE_TRACK", json_encode($obj));
    }

    public function notifyReorder()
    {
        $obj = new \stdClass();
        $obj->playlist_id = $this->id;

        $this->FazPublishNoMosquitto("PLAYLIST_REORDER", json_encode($obj));
    }





}
