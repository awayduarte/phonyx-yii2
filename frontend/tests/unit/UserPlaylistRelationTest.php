<?php

namespace frontend\tests\unit;

use common\models\User;

class UserPlaylistRelationTest extends \Codeception\Test\Unit
{
    public function testUserHasPlaylists()
    {
        $user = User::findOne(1);
        $this->assertNotNull($user->playlists);
    }
}