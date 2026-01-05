<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;

use common\models\User;
use common\models\Asset;

class ProfileController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['edit'],
                'rules' => [
                    [
                        
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Edit profile.
     */
    public function actionEdit()
    {
        /** @var User $model */
        $model = Yii::$app->user->identity;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());

            
            $model->profileFile = UploadedFile::getInstance($model, 'profileFile');

            if ($model->validate()) {

                if ($model->profileFile) {
                   
                    $basePath = Yii::getAlias('@frontend/web/uploads/profiles');
                    if (!is_dir($basePath)) {
                        mkdir($basePath, 0775, true);
                    }

                    $filename = uniqid('profile_') . '.' . $model->profileFile->extension;
                    $fullPath = $basePath . DIRECTORY_SEPARATOR . $filename;

                    if ($model->profileFile->saveAs($fullPath)) {
                        
                        $asset = new Asset();
                        $asset->path = '/uploads/profiles/' . $filename;
                        $asset->type = 'image';

                        if ($asset->save(false)) {
                            
                            $model->profile_asset_id = $asset->id;
                        }
                    }
                }

            
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Profile updated successfully.');
                    return $this->redirect(['profile/edit']);
                }
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }
}
