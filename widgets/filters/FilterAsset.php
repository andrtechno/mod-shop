<?php

namespace panix\mod\shop\widgets\filters;

use panix\engine\web\AssetBundle;
use yii\web\View;

/**
 * Class FilterAsset
 * @package panix\mod\shop\widgets\filters\assets
 */
class FilterAsset extends AssetBundle
{

    public $sourcePath = __DIR__.'/assets';

    public $js = [
        'js/filter.plugin.js',
        'js/filter.js',
    ];
    public $css = [
        'css/filter.css',
    ];
    public $depends = [
        'yii\jui\JuiAsset',
        'yii\web\JqueryAsset',
    ];
    public $jsOptions2 = [
        'position' => View::POS_END
    ];

}
