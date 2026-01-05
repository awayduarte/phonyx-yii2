<?php

namespace backend\modules\api\controllers;

use yii\rest\Controller;
use yii\web\Response;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // garante JSON sempre
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        // NÃO redirecionar para login
        unset($behaviors['authenticator']);

        return $behaviors;
    }
}
