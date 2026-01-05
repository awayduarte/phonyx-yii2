<?php

namespace frontend\controllers;

use Yii;
use common\models\Asset;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\data\ActiveDataProvider;

use common\models\Track;
use common\models\Artist;
use common\models\Genre;
use common\models\TrackFeaturedArtist;

class TrackController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'update', 'search'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                        'matchCallback' => function () {
                            $user = Yii::$app->user->identity;
                            return $user && $user->artist;
                        },
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($genre = null)
    {
        
        $genres = Genre::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

        if (empty($genres)) {
            return $this->render('index', [
                'genres' => [],
                'selectedGenre' => null,
                'dataProvider' => null,
            ]);
        }

       
        $selectedGenreId = $genre !== null ? (int)$genre : (int)$genres[0]->id;

      
        $selectedGenre = null;
        foreach ($genres as $g) {
            if ((int)$g->id === $selectedGenreId) {
                $selectedGenre = $g;
                break;
            }
        }
        if (!$selectedGenre) {
            $selectedGenre = $genres[0];
            $selectedGenreId = (int)$selectedGenre->id;
        }


        $query = Track::find()
            ->where(['genre_id' => $selectedGenreId])
            ->with(['artist', 'genre', 'audioAsset'])
            ->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
                'pageParam' => 'p',       
                'pageSizeParam' => false,
            ],
        ]);

        return $this->render('index', [
            'genres' => $genres,
            'selectedGenre' => $selectedGenre,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        $artist = $user->artist ?? null;

        if (!$artist) {
            throw new ForbiddenHttpException('Precisas de criar uma conta de artista primeiro.');
        }

        $model = new Track();
        $model->artist_id = $artist->id;

        $otherArtists = Artist::find()
            ->andWhere(['<>', 'id', $artist->id])
            ->all();
        $artistOptions = ArrayHelper::map($otherArtists, 'id', 'stage_name');

        $genres = Genre::find()->orderBy(['name' => SORT_ASC])->all();
        $genreOptions = ArrayHelper::map($genres, 'id', 'name');

        $moodOptions = [
            'chill' => 'Chill',
            'party' => 'Party',
            'focus' => 'Focus / Study',
            'sad' => 'Sad',
            'happy' => 'Happy',
            'dark' => 'Dark',
            'energetic' => 'Energetic',
        ];

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());

            $model->audioFile = UploadedFile::getInstance($model, 'audioFile');
            $model->coverFile = UploadedFile::getInstance($model, 'coverFile');

            if ($model->validate()) {

                $baseTrackPath = Yii::getAlias('@frontend/web/uploads/tracks');
                if (!is_dir($baseTrackPath)) {
                    mkdir($baseTrackPath, 0775, true);
                }

                $trackFilename = uniqid('track_') . '.' . $model->audioFile->extension;
                $trackFullPath = $baseTrackPath . DIRECTORY_SEPARATOR . $trackFilename;

                if (!$model->audioFile->saveAs($trackFullPath)) {
                    Yii::$app->session->setFlash('error', 'Failed to save audio file.');
                    return $this->render('create', compact('model', 'artistOptions', 'genreOptions', 'moodOptions'));
                }

                $audioAsset = new Asset();
                $audioAsset->path = '/uploads/tracks/' . $trackFilename;
                $audioAsset->type = 'audio';

                if (!$audioAsset->save(false)) {
                    Yii::$app->session->setFlash('error', 'Failed to create audio asset.');
                    return $this->render('create', compact('model', 'artistOptions', 'genreOptions', 'moodOptions'));
                }

                $model->audio_asset_id = $audioAsset->id;

                if (!$model->save(false)) {
                    Yii::$app->session->setFlash('error', 'Failed to save track.');
                    return $this->render('create', compact('model', 'artistOptions', 'genreOptions', 'moodOptions'));
                }

                if (is_array($model->featuredArtistIds)) {
                    foreach ($model->featuredArtistIds as $featId) {
                        if ($featId) {
                            $link = new TrackFeaturedArtist();
                            $link->track_id = $model->id;
                            $link->artist_id = (int) $featId;
                            $link->save(false);
                        }
                    }
                }

                Yii::$app->session->setFlash('success', 'Track uploaded successfully.');
                return $this->redirect(['artist/dashboard']);
            }

            Yii::$app->session->setFlash('error', 'Upload failed. Please check the form.');
        }

        return $this->render('create', [
            'model' => $model,
            'artistOptions' => $artistOptions,
            'genreOptions' => $genreOptions,
            'moodOptions' => $moodOptions,
        ]);
    }

    public function actionView($id)
    {
        $model = Track::find()
            ->with(['artist', 'featuredArtists', 'album', 'genre'])
            ->where(['id' => $id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Faixa não encontrada.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
{
    /** @var \common\models\User $user */
    $user = Yii::$app->user->identity;

    if (!$user || !$user->artist) {
        throw new ForbiddenHttpException('You must have an artist account to edit tracks.');
    }

    $model = Track::find()
        ->where(['id' => (int)$id, 'artist_id' => (int)$user->artist->id])
        ->one();

    if (!$model) {
        throw new NotFoundHttpException('Track not found.');
    }

    $genres = Genre::find()->orderBy(['name' => SORT_ASC])->all();
    $genreOptions = ArrayHelper::map($genres, 'id', 'name');

    if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {

        $model->audioFile = UploadedFile::getInstance($model, 'audioFile');
       
        $model->coverFile = UploadedFile::getInstance($model, 'coverFile');

        if ($model->validate()) {

            // ========= AUDIO UPDATE  =========
            if ($model->audioFile) {
                $baseTrackPath = Yii::getAlias('@frontend/web/uploads/tracks');
                if (!is_dir($baseTrackPath)) {
                    mkdir($baseTrackPath, 0775, true);
                }

                $trackFilename = uniqid('track_') . '.' . $model->audioFile->extension;
                $trackFullPath = $baseTrackPath . DIRECTORY_SEPARATOR . $trackFilename;

                if ($model->audioFile->saveAs($trackFullPath)) {
                    $relativePath = 'uploads/tracks/' . $trackFilename;

                   
                    $audioAsset = $model->audio_asset_id ? Asset::findOne((int)$model->audio_asset_id) : null;
                    if (!$audioAsset) {
                        $audioAsset = new Asset();
                        $audioAsset->type = 'audio';
                    }

                    $audioAsset->path = '/' . ltrim($relativePath, '/');

                    if ($audioAsset->save(false)) {
                        $model->audio_asset_id = (int)$audioAsset->id;

                      
                        $model->duration = null;
                    } else {
                        Yii::$app->session->setFlash('error', 'Failed to update audio asset.');
                        return $this->render('update', [
                            'model' => $model,
                            'genreOptions' => $genreOptions,
                        ]);
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to save audio file.');
                    return $this->render('update', [
                        'model' => $model,
                        'genreOptions' => $genreOptions,
                    ]);
                }
            }

           
            if ($model->coverFile) {
                Yii::$app->session->setFlash('warning', 'Track cover is not supported yet (no DB column). Upload ignored.');
            }

            $model->save(false);

            Yii::$app->session->setFlash('success', 'Track updated successfully.');
            return $this->redirect(['artist/dashboard']);
        }
    }

    return $this->render('update', [
        'model' => $model,
        'genreOptions' => $genreOptions,
    ]);
}
    public function actionSearch($q = '')
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $q = trim($q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }

        $tracks = Track::find()
            ->alias('t')
            ->joinWith(['artist a'])
            ->andWhere([
                'or',
                ['like', 't.title', $q],
                ['like', 'a.stage_name', $q],
            ])
            ->orderBy(['t.created_at' => SORT_DESC])
            ->distinct()
            ->limit(10)
            ->all();

        $out = [];
        foreach ($tracks as $t) {
            $out[] = [
                'id' => (int)$t->id,
                'title' => (string)$t->title,
                'subtitle' => (string)($t->artist->stage_name ?? ''),
            ];
        }

        return $out;
    }
}
