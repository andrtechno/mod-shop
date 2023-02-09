<?php

namespace panix\mod\shop\widgets\filters;

use panix\engine\web\AssetBundle;

/**
 * Class FilterAsset
 * @package panix\mod\shop\widgets\filters\assets
 */
class FilterAsset extends AssetBundle
{

    public $sourcePath = __DIR__.'/assets';

    public $js = [
        'js/filter.js',
    ];
    public $css = [
        'css/filter.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}