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
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow'   => true,
                        'roles'   => ['?', '@'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
        ];
    }

   
    public function actionIndex()
    {
     
        $genres = Genre::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

      
        // Fetch genres directly from DB (single source of truth)
$genres = Genre::find()
->orderBy(['name' => SORT_ASC])
->all();

// Group tracks by genre_id
$tracksByGenre = [];
foreach ($genres as $genre) {
$tracksByGenre[$genre->id] = Track::find()
    ->where(['genre_id' => $genre->id])
    ->with(['artist', 'genre', 'audioAsset'])
    ->orderBy(['created_at' => SORT_DESC])
    ->all();
}

return $this->render('index', [
'genres'        => $genres,        // Genre objects (automatic)
'tracksByGenre' => $tracksByGenre, // [genre_id => tracks[]]
]);

    }

   
    public function actionCreate()
    {
        $user   = Yii::$app->user->identity;
        $artist = $user->artist ?? null;

        if (!$artist) {
            throw new \yii\web\ForbiddenHttpException('Precisas de criar uma conta de artista primeiro.');
        }

        $model = new Track();
        $model->artist_id = $artist->id;

    
        $otherArtists = Artist::find()
            ->andWhere(['<>', 'id', $artist->id])
            ->all();
        $artistOptions = ArrayHelper::map($otherArtists, 'id', 'artist_name');


        $genres = Genre::find()->orderBy(['name' => SORT_ASC])->all();
        $genreOptions = ArrayHelper::map($genres, 'id', 'name');

   
        $moodOptions = [
            'chill'     => 'Chill',
            'party'     => 'Party',
            'focus'     => 'Focus / Study',
            'sad'       => 'Sad',
            'happy'     => 'Happy',
            'dark'      => 'Dark',
            'energetic' => 'Energetic',
        ];

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
        
            // Retrieve uploaded files
            $model->audioFile = UploadedFile::getInstance($model, 'audioFile');
            $model->coverFile = UploadedFile::getInstance($model, 'coverFile');
        
            if ($model->validate()) {
        
                /*1) Save audio file to disk*/
                $baseTrackPath = Yii::getAlias('@frontend/web/uploads/tracks');
                if (!is_dir($baseTrackPath)) {
                    mkdir($baseTrackPath, 0775, true);
                }
        
                $trackFilename = uniqid('track_') . '.' . $model->audioFile->extension;
                $trackFullPath = $baseTrackPath . DIRECTORY_SEPARATOR . $trackFilename;
        
                if (!$model->audioFile->saveAs($trackFullPath)) {
                    Yii::$app->session->setFlash('error', 'Failed to save audio file.');
                    return $this->render('create', compact('model','artistOptions','genreOptions','moodOptions'));
                }
        
                /*2) Create audio asset record*/
                $audioAsset = new Asset();
                $audioAsset->path = '/uploads/tracks/' . $trackFilename;
                $audioAsset->type = 'audio';
        
                if (!$audioAsset->save(false)) {
                    Yii::$app->session->setFlash('error', 'Failed to create audio asset.');
                    return $this->render('create', compact('model','artistOptions','genreOptions','moodOptions'));
                }
        
                // Link asset to track
                $model->audio_asset_id = $audioAsset->id;
        
                /*3) Save track*/
                if (!$model->save(false)) {
                    Yii::$app->session->setFlash('error', 'Failed to save track.');
                    return $this->render('create', compact('model','artistOptions','genreOptions','moodOptions'));
                }
        
                /* 4) Save featured artists*/
                if (is_array($model->featuredArtistIds)) {
                    foreach ($model->featuredArtistIds as $featId) {
                        if ($featId) {
                            $link = new TrackFeaturedArtist();
                            $link->track_id = $model->id;
                            $link->artist_id = (int)$featId;
                            $link->save(false);
                        }
                    }
                }
        
                Yii::$app->session->setFlash('success', 'Track uploaded successfully.');
                return $this->redirect(['artist/dashboard']);
            }
        
            // Validation failed
            Yii::$app->session->setFlash('error', 'Upload failed. Please check the form.');
        }
        
        

        return $this->render('create', [
            'model'         => $model,
            'artistOptions' => $artistOptions,
            'genreOptions'  => $genreOptions, // agora é [id => name]
            'moodOptions'   => $moodOptions,
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

    // Load track and make sure it belongs to the logged-in artist
    $model = Track::find()
        ->where(['id' => (int)$id, 'artist_id' => (int)$user->artist->id])
        ->one();

    if (!$model) {
        throw new NotFoundHttpException('Track not found.');
    }

    // Genres from DB
    $genres = Genre::find()->orderBy(['name' => SORT_ASC])->all();
    $genreOptions = ArrayHelper::map($genres, 'id', 'name');

    if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {

        // Optional file re-upload
        $model->audioFile = UploadedFile::getInstance($model, 'audioFile');
        $model->coverFile = UploadedFile::getInstance($model, 'coverFile');

        if ($model->validate()) {

            // If a new audio file was uploaded, replace file_path
            if ($model->audioFile) {
                $baseTrackPath = Yii::getAlias('@frontend/web/uploads/tracks');
                if (!is_dir($baseTrackPath)) {
                    mkdir($baseTrackPath, 0775, true);
                }

                $trackFilename = uniqid('track_') . '.' . $model->audioFile->extension;
                $trackFullPath = $baseTrackPath . DIRECTORY_SEPARATOR . $trackFilename;

                if ($model->audioFile->saveAs($trackFullPath)) {
                    $model->file_path = '/uploads/tracks/' . $trackFilename;
                }
            }

            // If a new cover was uploaded, replace cover_path
            if ($model->coverFile) {
                $baseCoverPath = Yii::getAlias('@frontend/web/uploads/covers');
                if (!is_dir($baseCoverPath)) {
                    mkdir($baseCoverPath, 0775, true);
                }

                $coverFilename = uniqid('cover_') . '.' . $model->coverFile->extension;
                $coverFullPath = $baseCoverPath . DIRECTORY_SEPARATOR . $coverFilename;

                if ($model->coverFile->saveAs($coverFullPath)) {
                    $model->cover_path = '/uploads/covers/' . $coverFilename;
                }
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

}
