<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/site.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'hail812\adminlte3\assets\AdminLteAsset',
    ];
    
}
