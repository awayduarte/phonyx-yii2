<?php

use yii\db\Migration;

class m251116_205543_create_initial_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // USERS ------------------------------------------------------------
        $this->createTable('users', [
            'id' => $this->primaryKey(),
            'email' => $this->string(150)->notNull()->unique(),
            'username' => $this->string(64)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'display_name' => $this->string(120),
            'status' => $this->tinyInteger()->notNull()->defaultValue(0), // ACTIVE=0
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')
                ->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // ROLES ------------------------------------------------------------
        $this->createTable('roles', [
            'id' => $this->primaryKey(),
            'code' => $this->string(30)->notNull()->unique(),
            'name' => $this->string(60)->notNull(),
            'description' => $this->text(),
        ]);

        // USER ROLES -------------------------------------------------------
        $this->createTable('user_roles', [
            'user_id' => $this->integer()->notNull(),
            'role_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(user_id, role_id)',
        ]);

        $this->addForeignKey('fk_user_roles_user', 'user_roles', 'user_id', 'users', 'id', 'CASCADE');
        $this->addForeignKey('fk_user_roles_role', 'user_roles', 'role_id', 'roles', 'id', 'CASCADE');

        // AUTH TOKENS ------------------------------------------------------
        $this->createTable('auth_tokens', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token_hash' => $this->string(128)->notNull()->unique(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'expires_at' => $this->timestamp()->notNull(),
        ]);

        $this->addForeignKey('fk_auth_tokens_user', 'auth_tokens', 'user_id', 'users', 'id', 'CASCADE');

        // ASSETS -----------------------------------------------------------
        $this->createTable('assets', [
            'id' => $this->primaryKey(),
            'type' => $this->tinyInteger()->notNull(), // AUDIO=0, IMAGE=1
            'storage_path' => $this->string(500)->notNull(),
            'mime_type' => $this->string(120)->notNull(),
            'duration_sec' => $this->integer(),
            'size_bytes' => $this->bigInteger(),
            'play_count' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // ARTISTS ----------------------------------------------------------
        $this->createTable('artists', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->unique(),
            'stage_name' => $this->string(120)->notNull(),
            'bio' => $this->text(),
            'profile_asset_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')
                ->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_artists_user', 'artists', 'user_id', 'users', 'id', 'CASCADE');
        $this->addForeignKey('fk_artists_profile_asset', 'artists', 'profile_asset_id', 'assets', 'id');

        // USER FOLLOWS -----------------------------------------------------
        $this->createTable('user_follows', [
            'follower_user_id' => $this->integer()->notNull(),
            'artist_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(follower_user_id, artist_id)',
        ]);

        $this->addForeignKey('fk_user_follows_user', 'user_follows', 'follower_user_id', 'users', 'id', 'CASCADE');
        $this->addForeignKey('fk_user_follows_artist', 'user_follows', 'artist_id', 'artists', 'id', 'CASCADE');

        // GENRES -----------------------------------------------------------
        $this->createTable('genres', [
            'id' => $this->primaryKey(),
            'name' => $this->string(80)->notNull()->unique(),
            'slug' => $this->string(80)->notNull()->unique(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // ALBUMS -----------------------------------------------------------
        $this->createTable('albums', [
            'id' => $this->primaryKey(),
            'title' => $this->string(200)->notNull(),
            'main_artist_id' => $this->integer()->notNull(),
            'cover_asset_id' => $this->integer(),
            'release_date' => $this->date(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')
                ->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_albums_main_artist', 'albums', 'main_artist_id', 'artists', 'id', 'CASCADE');
        $this->addForeignKey('fk_albums_cover_asset', 'albums', 'cover_asset_id', 'assets', 'id');

        // TRACKS -----------------------------------------------------------
        $this->createTable('tracks', [
            'id' => $this->primaryKey(),
            'title' => $this->string(200)->notNull(),
            'album_id' => $this->integer(),
            'audio_asset_id' => $this->integer()->notNull(),
            'cover_asset_id' => $this->integer(),
            'genre_id' => $this->integer(),
            'duration_sec' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')
                ->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_tracks_album', 'tracks', 'album_id', 'albums', 'id', 'SET NULL');
        $this->addForeignKey('fk_tracks_audio_asset', 'tracks', 'audio_asset_id', 'assets', 'id');
        $this->addForeignKey('fk_tracks_cover_asset', 'tracks', 'cover_asset_id', 'assets', 'id');
        $this->addForeignKey('fk_tracks_genre', 'tracks', 'genre_id', 'genres', 'id');

        // TRACK ARTISTS ----------------------------------------------------
        $this->createTable('track_artists', [
            'track_id' => $this->integer()->notNull(),
            'artist_id' => $this->integer()->notNull(),
            'role' => $this->string(40)->notNull()->defaultValue('PRIMARY'),
            'PRIMARY KEY(track_id, artist_id, role)',
        ]);

        $this->addForeignKey('fk_track_artists_track', 'track_artists', 'track_id', 'tracks', 'id', 'CASCADE');
        $this->addForeignKey('fk_track_artists_artist', 'track_artists', 'artist_id', 'artists', 'id', 'CASCADE');

        // PLAYLISTS --------------------------------------------------------
        $this->createTable('playlists', [
            'id' => $this->primaryKey(),
            'owner_user_id' => $this->integer()->notNull(),
            'title' => $this->string(160)->notNull(),
            'is_public' => $this->tinyInteger()->defaultValue(1),
            'cover_asset_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')
                ->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_playlists_owner', 'playlists', 'owner_user_id', 'users', 'id', 'CASCADE');
        $this->addForeignKey('fk_playlists_cover_asset', 'playlists', 'cover_asset_id', 'assets', 'id');

        // PLAYLIST TRACKS --------------------------------------------------
        $this->createTable('playlist_tracks', [
            'playlist_id' => $this->integer()->notNull(),
            'track_id' => $this->integer()->notNull(),
            'position' => $this->integer()->notNull(),
            'added_by' => $this->integer()->notNull(),
            'added_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(playlist_id, track_id)',
        ]);

        $this->createIndex('idx_playlist_position', 'playlist_tracks', ['playlist_id', 'position'], true);

        $this->addForeignKey('fk_playlist_tracks_playlist', 'playlist_tracks', 'playlist_id', 'playlists', 'id', 'CASCADE');
        $this->addForeignKey('fk_playlist_tracks_track', 'playlist_tracks', 'track_id', 'tracks', 'id', 'CASCADE');
        $this->addForeignKey('fk_playlist_tracks_user', 'playlist_tracks', 'added_by', 'users', 'id');

        // USER LIKES -------------------------------------------------------
        $this->createTable('user_likes', [
            'user_id' => $this->integer()->notNull(),
            'track_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(user_id, track_id)',
        ]);

        $this->addForeignKey('fk_user_likes_user', 'user_likes', 'user_id', 'users', 'id', 'CASCADE');
        $this->addForeignKey('fk_user_likes_track', 'user_likes', 'track_id', 'tracks', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_likes');
        $this->dropTable('playlist_tracks');
        $this->dropTable('playlists');
        $this->dropTable('track_artists');
        $this->dropTable('tracks');
        $this->dropTable('albums');
        $this->dropTable('genres');
        $this->dropTable('user_follows');
        $this->dropTable('artists');
        $this->dropTable('assets');
        $this->dropTable('auth_tokens');
        $this->dropTable('user_roles');
        $this->dropTable('roles');
        $this->dropTable('users');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251116_205543_create_initial_schema cannot be reverted.\n";

        return false;
    }
    */
}
