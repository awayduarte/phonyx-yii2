<?php

namespace backend\modules\api\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionPing()
    {
        return ['ok' => true, 'app' => 'api', 'time' => date('c')];
    }
}
