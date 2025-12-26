<?php
namespace app\assets;

use yii\web\AssetBundle;

class PhonyxAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';
    public $css = [
        'css/phonyx.css',
    ];
    public $js = [
        'js/phonyx-player.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\bootstrap5\BootstrapPluginAsset',
    ];
}
