<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;

use common\models\Album;
use common\models\Track;
use common\models\Asset;
use yii\db\Expression;



class AlbumController extends Controller
{
    private function requireArtist()
    {
        /** @var \common\models\User|null $user */
        $user = Yii::$app->user->identity;

        if (!$user || !$user->artist) {
            throw new ForbiddenHttpException('You must have an artist account.');
        }

        return $user->artist;
    }

    private function artistTrackOptions(int $artistId): array
    {
        $artistTracks = Track::find()
            ->where(['artist_id' => $artistId])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

  
        return ArrayHelper::map($artistTracks, 'id', 'title');
    }

    private function syncAlbumTracks(int $artistId, int $albumId, array $chosenTrackIds): void
    {
        $chosenTrackIds = array_values(array_filter(array_map('intval', (array)$chosenTrackIds)));

       
        Track::updateAll(
            ['album_id' => null],
            ['and',
                ['artist_id' => $artistId],
                ['album_id' => $albumId],
                ['not in', 'id', $chosenTrackIds ?: [0]],
            ]
        );

        if (!empty($chosenTrackIds)) {
            Track::updateAll(
                ['album_id' => $albumId],
                ['and',
                    ['artist_id' => $artistId],
                    ['in', 'id', $chosenTrackIds],
                ]
            );
        }
    }

    public function actionCreate()
    {
        $artist = $this->requireArtist();
    
        $model = new Album();
        $model->artist_id = (int) $artist->id;
    
        $trackOptions = $this->artistTrackOptions((int)$artist->id);
        $selectedTrackIds = [];
    
        if ($model->load(Yii::$app->request->post())) {
    
            $selectedTrackIds = Yii::$app->request->post('trackIds', []);
            $selectedTrackIds = array_values(array_filter(array_map('intval', (array)$selectedTrackIds)));
    
            // upload opcional
            if (property_exists($model, 'coverFile')) {
                $model->coverFile = UploadedFile::getInstance($model, 'coverFile');
            }
    
            // remover capa (checkbox no form: name="removeCover" value="1")
            $removeCover = (int)Yii::$app->request->post('removeCover', 0) === 1;
    
            if ($model->validate()) {
    
                if ($removeCover) {
                    $model->cover_asset_id = null;
                }
    
                // Se fez upload de capa, cria um Asset e guarda cover_asset_id
                if (property_exists($model, 'coverFile') && $model->coverFile) {
                    $baseCoverPath = Yii::getAlias('@frontend/web/uploads/album-covers');
                    if (!is_dir($baseCoverPath)) {
                        mkdir($baseCoverPath, 0775, true);
                    }
    
                    $coverFilename = uniqid('album_') . '.' . $model->coverFile->extension;
                    $coverFullPath = $baseCoverPath . DIRECTORY_SEPARATOR . $coverFilename;
    
                    if ($model->coverFile->saveAs($coverFullPath)) {
                        $relativePath = 'uploads/album-covers/' . $coverFilename;
    
                        $asset = new Asset();
                        $asset->path = $relativePath;
    
                        // se tiveres type no Asset
                        if ($asset->hasAttribute('type')) {
                            $asset->type = 'image';
                        }
    
                        // se tiveres created_by_user_id no Asset
                        if ($asset->hasAttribute('created_by_user_id')) {
                            $asset->created_by_user_id = (int)Yii::$app->user->id;
                        }
    
                        // se tiveres timestamps no Asset
                        if ($asset->hasAttribute('created_at') && empty($asset->created_at)) {
                            $asset->created_at = new Expression('NOW()');
                        }
                        if ($asset->hasAttribute('updated_at')) {
                            $asset->updated_at = new Expression('NOW()');
                        }
    
                        $asset->save(false);
    
                        $model->cover_asset_id = (int)$asset->id;
                    }
                }
    
                $model->save(false);
    
                // associar as tracks ao álbum
                $this->syncAlbumTracks((int)$artist->id, (int)$model->id, $selectedTrackIds);
    
                Yii::$app->session->setFlash('success', 'Album created successfully.');
                return $this->redirect(['artist/dashboard']);
            }
        }
    
        return $this->render('create', [
            'model' => $model,
            'trackOptions' => $trackOptions,
            'selectedTrackIds' => $selectedTrackIds,
        ]);
    }
    

    public function actionUpdate($id)
{
    $artist = $this->requireArtist();

    $model = Album::find()
        ->where(['id' => (int)$id, 'artist_id' => (int)$artist->id])
        ->one();

    if (!$model) {
        throw new NotFoundHttpException('Album not found.');
    }

    $trackOptions = $this->artistTrackOptions((int)$artist->id);

    // tracks já no álbum
    $selectedTrackIds = Track::find()
        ->select('id')
        ->where([
            'artist_id' => (int)$artist->id,
            'album_id' => (int)$model->id,
        ])
        ->column();

    if ($model->load(Yii::$app->request->post())) {

        // tracks escolhidas
        $selectedTrackIds = Yii::$app->request->post('trackIds', []);
        $selectedTrackIds = array_values(array_filter(array_map('intval', (array)$selectedTrackIds)));

        // upload opcional
        if (property_exists($model, 'coverFile')) {
            $model->coverFile = UploadedFile::getInstance($model, 'coverFile');
        }

        $removeCover = (int)Yii::$app->request->post('removeCover', 0) === 1;

        if ($model->validate()) {

            // remover capa
            if ($removeCover) {
                $model->cover_asset_id = null;
            }

            // se fez upload de nova capa -> cria asset e atribui cover_asset_id
            if (property_exists($model, 'coverFile') && $model->coverFile) {

                $baseCoverPath = Yii::getAlias('@frontend/web/uploads/album-covers');
                if (!is_dir($baseCoverPath)) {
                    mkdir($baseCoverPath, 0775, true);
                }

                $coverFilename = uniqid('album_') . '.' . $model->coverFile->extension;
                $coverFullPath = $baseCoverPath . DIRECTORY_SEPARATOR . $coverFilename;

                if ($model->coverFile->saveAs($coverFullPath)) {
                    $relativePath = 'uploads/album-covers/' . $coverFilename;

                    // Reutiliza asset existente se houver (melhor), senão cria novo
                    $asset = null;
                    if (!empty($model->cover_asset_id)) {
                        $asset = Asset::findOne((int)$model->cover_asset_id);
                    }
                    if (!$asset) {
                        $asset = new Asset();

                        if ($asset->hasAttribute('created_by_user_id')) {
                            $asset->created_by_user_id = (int)Yii::$app->user->id;
                        }
                        if ($asset->hasAttribute('created_at') && empty($asset->created_at)) {
                            $asset->created_at = new Expression('NOW()');
                        }
                    }

                    $asset->path = $relativePath;

                    if ($asset->hasAttribute('type')) {
                        $asset->type = 'image';
                    }
                    if ($asset->hasAttribute('updated_at')) {
                        $asset->updated_at = new Expression('NOW()');
                    }

                    $asset->save(false);
                    $model->cover_asset_id = (int)$asset->id;
                }
            }

            $model->save(false);

            // sync das faixas para o álbum
            $this->syncAlbumTracks((int)$artist->id, (int)$model->id, $selectedTrackIds);

            Yii::$app->session->setFlash('success', 'Album updated successfully.');
            return $this->redirect(['artist/dashboard']);
        }
    }

    return $this->render('update', [
        'model' => $model,
        'trackOptions' => $trackOptions,
        'selectedTrackIds' => $selectedTrackIds,
    ]);
}

    public function actionDelete($id)
    {
        $artist = $this->requireArtist();

        $model = Album::find()
            ->where(['id' => (int) $id, 'artist_id' => (int) $artist->id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Album not found.');
        }

       
        Track::updateAll(
            ['album_id' => null],
            ['and', ['artist_id' => (int)$artist->id], ['album_id' => (int)$model->id]]
        );

        $model->delete();

        Yii::$app->session->setFlash('success', 'Album deleted successfully.');
        return $this->redirect(['artist/dashboard']);
    }

    public function actionView($id)
{
    $album = Album::findOne((int)$id);
    if (!$album) {
        throw new NotFoundHttpException('Album not found.');
    }

    // Buscar faixas do álbum (assumindo track.album_id)
    $tracks = Track::find()
        ->where(['album_id' => (int)$album->id])
        ->orderBy(['created_at' => SORT_ASC])
        ->all();

    return $this->render('view', [
        'album' => $album,
        'tracks' => $tracks,
    ]);
}
}
