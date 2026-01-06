<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class PlaylistCest
{
    public function createPlaylist(FunctionalTester $I)
    {
        $I->amLoggedInAs(1);
        $I->amOnPage('/playlist/create');

        $I->submitForm('#playlist-form', [
            'Playlist[title]' => 'Playlist Teste',
        ]);

        $I->see('Playlist criada!');
    }
}
