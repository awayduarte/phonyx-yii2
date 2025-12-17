<?php

use yii\db\Migration;
use common\models\User;

/**
 * Class m251212_190054_seed_initial_users
 */
class m251212_190054_seed_initial_users extends Migration
{
    public function safeUp()
    {
        $security = Yii::$app->security;

        // ---------------------------
        // ADMIN
        // ---------------------------
        $this->insert('user', [
            'username' => 'admin',
            'email' => 'admin@phonyx.com',
            'password_hash' => $security->generatePasswordHash('123456'),
            'auth_key' => $security->generateRandomString(),
            'access_token' => $security->generateRandomString(40),
            'role' => User::ROLE_ADMIN,
            'status' => 10,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // ---------------------------
        // ARTIST USER
        // ---------------------------
        $this->insert('user', [
            'username' => 'artist1',
            'email' => 'artist@phonyx.com',
            'password_hash' => $security->generatePasswordHash('123456'),
            'auth_key' => $security->generateRandomString(),
            'access_token' => $security->generateRandomString(40),
            'role' => User::ROLE_ARTIST,
            'status' => 10,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // get last inserted user_id (artist)
        $artistUserId = $this->db->getLastInsertID();

        // create artist profile
        $this->insert('artist', [
            'user_id' => $artistUserId,
            'stage_name' => 'Artist One',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // ---------------------------
        // NORMAL USER
        // ---------------------------
        $this->insert('user', [
            'username' => 'user1',
            'email' => 'user@phonyx.com',
            'password_hash' => $security->generatePasswordHash('123456'),
            'auth_key' => $security->generateRandomString(),
            'access_token' => $security->generateRandomString(40),
            'role' => User::ROLE_USER,
            'status' => 10,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function safeDown()
    {
        // remove artist profile first
        $this->delete('artist', [
            'user_id' => (new \yii\db\Query())
                ->select('id')
                ->from('user')
                ->where(['email' => 'artist@phonyx.com'])
        ]);

        // remove all users created
        $this->delete('user', [
            'email' => [
                'admin@phonyx.com',
                'artist@phonyx.com',
                'user@phonyx.com'
            ]
        ]);
    }
}
