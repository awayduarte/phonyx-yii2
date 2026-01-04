<?php

namespace api\controllers\v1;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionPing()
    {
        return ['ok' => true, 'app' => 'api', 'time' => date('c')];
    }
}
