<?php

namespace frontend\tests\unit;

use common\models\Track;

class TrackValidationTest extends \Codeception\Test\Unit
{
    public function testTrackWithoutTitleFails()
    {
        $track = new Track();
        $track->artist_id = 1;
        $track->duration = 180;

        $this->assertFalse($track->validate());
    }
}
