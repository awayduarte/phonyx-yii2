<?php

use yii\db\Migration;
use yii\db\Query;

class m260104_000001_seed_artist_assets extends Migration
{
    public function safeUp()
    {
        // 1) Encontrar o user e o artist pelo email (robusto)
        $artistUser = (new Query())
            ->from('user')
            ->select(['id', 'email'])
            ->where(['email' => 'artist@phonyx.com'])
            ->one($this->db);

        if (!$artistUser) {
            throw new \RuntimeException("Não encontrei o user 'artist@phonyx.com'. Corre primeiro a migration de seed de users.");
        }

        $artist = (new Query())
            ->from('artist')
            ->select(['id', 'user_id', 'avatar_asset_id'])
            ->where(['user_id' => (int)$artistUser['id']])
            ->one($this->db);

        if (!$artist) {
            throw new \RuntimeException("Não encontrei o registo na tabela 'artist' para o user_id=" . (int)$artistUser['id']);
        }

        $artistUserId = (int)$artistUser['id'];
        $artistId     = (int)$artist['id'];

        // 2) Criar assets (imagens + audios) pertencentes ao artista
        // Usa paths "seed" para conseguires apagar facilmente no safeDown.
        $seedAssets = [
            // IMAGENS
            ['path' => 'uploads/seed/image/artist-avatar.png', 'type' => 'image'],
            ['path' => 'uploads/seed/image/album-cover.png',   'type' => 'image'],

            // AUDIOS
            ['path' => 'uploads/seed/audio/track-01.mp3', 'type' => 'audio'],
            ['path' => 'uploads/seed/audio/track-02.mp3', 'type' => 'audio'],
            ['path' => 'uploads/seed/audio/track-03.mp3', 'type' => 'audio'],
        ];

        $assetIdsByPath = [];

        foreach ($seedAssets as $a) {
            $this->insert('asset', [
                'path' => $a['path'],
                'type' => $a['type'],
            ]);
            $assetIdsByPath[$a['path']] = (int)$this->db->getLastInsertID();
        }

        $avatarAssetId = $assetIdsByPath['uploads/seed/image/artist-avatar.png'];
        $coverAssetId  = $assetIdsByPath['uploads/seed/image/album-cover.png'];

        // 3) Atualizar avatar do artista (artist.avatar_asset_id)
        $this->update('artist',
            ['avatar_asset_id' => $avatarAssetId],
            ['id' => $artistId]
        );

        // 4) Criar 1 album de seed (opcional mas útil)
        $this->insert('album', [
            'artist_id' => $artistId,
            'title' => 'Seed Album',
            'cover_asset_id' => $coverAssetId,
            // se a tua tabela album tiver mais colunas obrigatórias, adiciona aqui
        ]);
        $albumId = (int)$this->db->getLastInsertID();

        // 5) Criar tracks e associar audio_asset_id + artist_id (+ album_id)
        // duration em segundos (ex.: 2:34 = 154)
        $tracks = [
            [
                'title' => 'Seed Track 01',
                'audio_asset_path' => 'uploads/seed/audio/track-01.mp3',
                'duration' => 154,
                'genre_id' => null,
            ],
            [
                'title' => 'Seed Track 02',
                'audio_asset_path' => 'uploads/seed/audio/track-02.mp3',
                'duration' => 201,
                'genre_id' => null,
            ],
            [
                'title' => 'Seed Track 03',
                'audio_asset_path' => 'uploads/seed/audio/track-03.mp3',
                'duration' => 189,
                'genre_id' => null,
            ],
        ];

        foreach ($tracks as $t) {
            $this->insert('track', [
                'artist_id' => $artistId,
                'album_id' => $albumId,
                'title' => $t['title'],
                'audio_asset_id' => $assetIdsByPath[$t['audio_asset_path']],
                'duration' => (int)$t['duration'],
                'genre_id' => $t['genre_id'],
            ]);
        }
    }

    public function safeDown()
    {
        $artistUser = (new Query())
            ->from('user')
            ->select(['id'])
            ->where(['email' => 'artist@phonyx.com'])
            ->one($this->db);
    
        if (!$artistUser) return true;
    
        $artist = (new Query())
            ->from('artist')
            ->select(['id'])
            ->where(['user_id' => (int)$artistUser['id']])
            ->one($this->db);
    
        if (!$artist) return true;
    
        $artistUserId = (int)$artistUser['id'];
        $artistId     = (int)$artist['id'];
    
        // apagar tracks seed
        $this->delete('track', [
            'artist_id' => $artistId,
            'title' => ['Seed Track 01', 'Seed Track 02', 'Seed Track 03'],
        ]);
    
        // apagar album seed
        $this->delete('album', [
            'artist_id' => $artistId,
            'title' => 'Seed Album',
        ]);
    
        // limpar avatar se for seed
        $avatarId = (new Query())
            ->from('artist')
            ->select('avatar_asset_id')
            ->where(['id' => $artistId])
            ->scalar($this->db);
    
        if ($avatarId) {
            $avatarPath = (new Query())
                ->from('asset')
                ->select('path')
                ->where(['id' => (int)$avatarId])
                ->scalar($this->db);
    
            if (is_string($avatarPath) && strpos($avatarPath, 'uploads/seed/image/') === 0) {
                $this->update('artist', ['avatar_asset_id' => null], ['id' => $artistId]);
            }
        }
    
        
        $this->delete('asset', "path LIKE 'uploads/seed/%'");
    
        return true;
    }
    
}
