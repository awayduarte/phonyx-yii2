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
                        // Only logged users can edit profile
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Edit profile (username/email + profile picture).
     */
    public function actionEdit()
    {
        /** @var User $model */
        $model = Yii::$app->user->identity;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());

            // Handle profile image upload (optional)
            $model->profileFile = UploadedFile::getInstance($model, 'profileFile');

            if ($model->validate()) {

                if ($model->profileFile) {
                    // 1) Save file to /frontend/web/uploads/profiles
                    $basePath = Yii::getAlias('@frontend/web/uploads/profiles');
                    if (!is_dir($basePath)) {
                        mkdir($basePath, 0775, true);
                    }

                    $filename = uniqid('profile_') . '.' . $model->profileFile->extension;
                    $fullPath = $basePath . DIRECTORY_SEPARATOR . $filename;

                    if ($model->profileFile->saveAs($fullPath)) {
                        // 2) Create asset record
                        $asset = new Asset();
                        $asset->path = '/uploads/profiles/' . $filename;
                        $asset->type = 'image';

                        if ($asset->save(false)) {
                            // 3) Link asset to user
                            $model->profile_asset_id = $asset->id;
                        }
                    }
                }

                // Save user basic fields + profile_asset_id
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
