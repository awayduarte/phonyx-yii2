<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

use frontend\models\EditProfileForm;
use common\models\Asset;

class UserController extends Controller
{
    public $layout = 'main'; 

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['profile', 'account', 'settings', 'edit-profile'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Logged-in users only
                    ],
                ],
            ],
        ];
    }

    /**
     * Logged-in user
     */
    public function actionProfile()
{
    $this->layout = 'main'; 

    $user = Yii::$app->user->identity;
    if (!$user) return $this->redirect(['site/login']);


    $this->view->params['bodyClass'] = 'page-profile';

    return $this->render('profile', [
        'user' => $user,
    ]);
}


    public function actionAccount()
    {
        return $this->redirect(['profile']);
    }

    public function actionSettings()
    {
        return $this->redirect(['profile']);
    }

    /**
     * Edit profile 
     */
    public function actionEditProfile()
    {
        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;

       
        $model = new EditProfileForm($user);

        if (Yii::$app->request->isPost) {

            
            if ($model->load(Yii::$app->request->post())) {
                
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

                                
                                $user->profile_asset_id = $asset->id;


                                if ($user->artist) {
                                    $user->artist->avatar_asset_id = $asset->id;
                                    $user->artist->save(false);
                                }
                            }
                        }
                    }

                    $user->username = $model->username;
                    $user->email = $model->email;


                    if ($user->save(false)) {
                        Yii::$app->session->setFlash('success', 'Profile updated successfully.');
                        return $this->refresh();
                    }

                }
            }
        }

        return $this->render('edit-profile', [
            'model' => $model,
            'user'  => $user,
        ]);
    }
}
