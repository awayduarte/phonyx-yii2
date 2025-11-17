<?php

namespace backend\assets;

use yii\web\AssetBundle;

class AdminLteAsset extends AssetBundle
{
    public $basePath = '@webroot/adminlte';
    public $baseUrl = '@web/adminlte';

    public $css = [
        'plugins/fontawesome-free/css/all.min.css',
        'dist/css/adminlte.min.css',
    ];

    public $js = [
        'plugins/bootstrap/js/bootstrap.bundle.min.js',
        'dist/js/adminlte.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}
