<?php
namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Track;

class TrackArtistRelationTest extends Unit
{
    public function testTrackHasArtistRelation()
    {
        // Precisa de existir pelo menos 1 Track na BD de teste
        $track = Track::find()->with('artist')->one();

        $this->assertNotNull($track, 'There should be at least one Track in test DB');
        $this->assertNotNull($track->artist, 'Track should have an artist relation loaded');
    }
}
