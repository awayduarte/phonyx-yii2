<?php

use yii\db\Migration;

class m260105_154523_create_track_featured_artist_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%track_featured_artist}}', [
            'id' => $this->primaryKey(),
            'track_id' => $this->integer()->notNull(),
            'artist_user_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull()->defaultValue(time()),
        ]);

        $this->createIndex(
            'uq_track_featured_artist_track_artist',
            '{{%track_featured_artist}}',
            ['track_id', 'artist_user_id'],
            true
        );

        $this->addForeignKey(
            'fk_track_featured_artist_track',
            '{{%track_featured_artist}}',
            'track_id',
            '{{%track}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_track_featured_artist_user',
            '{{%track_featured_artist}}',
            'artist_user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_track_featured_artist_user', '{{%track_featured_artist}}');
        $this->dropForeignKey('fk_track_featured_artist_track', '{{%track_featured_artist}}');
        $this->dropTable('{{%track_featured_artist}}');
    }
}
