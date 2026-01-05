<?php

use yii\db\Migration;

class m260105_155105_fix_track_featured_artist_artist_id extends Migration
{
    public function safeUp()
    {
      
        $this->renameColumn('{{%track_featured_artist}}', 'artist_user_id', 'artist_id');

        $this->addForeignKey(
            'fk_track_featured_artist_artist',
            '{{%track_featured_artist}}',
            'artist_id',
            '{{%artist}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_track_featured_artist_artist', '{{%track_featured_artist}}');

        $this->renameColumn('{{%track_featured_artist}}', 'artist_id', 'artist_user_id');

        
    }
}
