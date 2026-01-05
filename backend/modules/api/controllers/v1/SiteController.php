<?php

namespace backend\modules\api\controllers\v1;

use backend\modules\api\controllers\ApiController;

class SiteController extends ApiController
{
    public function actionPing()
    {
        return ['ok' => true, 'app' => 'backend-api', 'time' => date('c')];
    }
}
