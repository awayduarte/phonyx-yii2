<?php

namespace backend\modules\api;

use Yii;
use yii\web\Response;

/**
 * api module definition class
 */

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'backend\modules\api\controllers';

    public function init()
    {
        parent::init();

       
        \Yii::$app->user->enableSession = false;
        \Yii::$app->user->loginUrl = null;
        
        
        Yii::$app->response->format = Response::FORMAT_JSON;
    }
}
