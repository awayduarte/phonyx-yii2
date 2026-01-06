<?php

namespace frontend\tests\unit;

use common\models\Playlist;

class PlaylistCreateTest extends \Codeception\Test\Unit
{
    public function testCreatePlaylist()
    {
        $playlist = new Playlist();
        $playlist->title = 'Minha Playlist';   // usa o nome correto da coluna
        $playlist->user_id = 1;

        if (!$playlist->save()) {
            var_dump($playlist->getErrors());
        }

        $this->assertTrue($playlist->save());
    }


}
