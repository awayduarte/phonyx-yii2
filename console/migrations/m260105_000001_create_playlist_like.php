<?php

use yii\db\Migration;

class m260105_000001_create_playlist_like extends Migration
{
    public function safeUp()
    {
        // Create pivot table: user likes playlist
        $this->createTable('{{%playlist_like}}', [
            'playlist_id' => $this->integer()->notNull(),
            'user_id'     => $this->integer()->notNull(),
            'created_at'  => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(playlist_id, user_id)',
        ]);

        // FK -> playlist
        $this->addForeignKey(
            'fk_playlist_like_playlist',
            '{{%playlist_like}}',
            'playlist_id',
            '{{%playlist}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // FK -> user
        $this->addForeignKey(
            'fk_playlist_like_user',
            '{{%playlist_like}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Helpful indexes
        $this->createIndex('idx_playlist_like_user', '{{%playlist_like}}', 'user_id');
        $this->createIndex('idx_playlist_like_playlist', '{{%playlist_like}}', 'playlist_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%playlist_like}}');
    }
}
