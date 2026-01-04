<?php

namespace common\tests\unit\models;

use common\models\User;
use common\models\Artist;
use common\models\Genre;
use common\models\Track;

class TrackValidationTest extends \Codeception\Test\Unit
{
    public function testTrackRequiredFields()
    {
        
        $track = new Track();
        $this->assertFalse($track->validate(), 'Track should fail validation when empty');

        
        $user = new User();
        $user->username = 'test_user_' . time();
        $user->email = 'test_' . time() . '@example.com';
        $user->password = '123456'; 
        $user->role = 'user';       
        $user->status = 10;       
        $this->assertTrue($user->save(false), 'User should save');

        $artist = new Artist();
        $artist->stage_name = 'Test Artist';
        $artist->bio = 'bio';
        $artist->user_id = $user->id;       
        $artist->created_at = time();
        $artist->updated_at = time();
        $this->assertTrue($artist->save(false), 'Artist should save');

        $genre = new Genre();
        $genre->name = 'Test Genre';
        $genre->created_at = time();
        $genre->updated_at = time();
        $this->assertTrue($genre->save(false), 'Genre should save');

       
        $track2 = new Track();
        $track2->artist_id = $artist->id;
        $track2->title = 'Test Track';          
        $track2->genre_id = $genre->id;

        
        $this->assertTrue($track2->validate(), 'Track should validate with required fields');
    }
}
