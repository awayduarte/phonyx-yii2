<?php

namespace common\tests\unit\models;
use Yii;
use Codeception\Test\Unit;
use common\models\Playlist;
use common\models\User;

class PlaylistValidationTest extends Unit
{
    private function createUserId(): int
    {
        $u = new User();

       
        if ($u->hasAttribute('username')) $u->username = 'test_user_' . uniqid();
        if ($u->hasAttribute('email')) $u->email = 'test_' . uniqid() . '@example.com';
        if ($u->hasAttribute('auth_key')) $u->auth_key = Yii::$app->security->generateRandomString();
        if ($u->hasAttribute('password_hash')) $u->password_hash = Yii::$app->security->generatePasswordHash('123456');
        if ($u->hasAttribute('created_at')) $u->created_at = time();
        if ($u->hasAttribute('updated_at')) $u->updated_at = time();
        if ($u->hasAttribute('status')) $u->status = 10;

        $u->save(false);
        return (int)$u->id;
    }

    public function testPlaylistRequiresNameAndUser()
    {
        $playlist = new Playlist();

        
        $this->assertFalse($playlist->validate(), 'Playlist should fail when empty');

     
        if ($playlist->hasAttribute('title')) {
            $playlist->title = 'Minha Playlist';
        }

        if ($playlist->hasAttribute('user_id')) {
            $playlist->user_id = $this->createUserId();
        }

        $this->assertTrue($playlist->validate(), 'Playlist should validate with required fields');
    }
}
