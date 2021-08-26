<?php

namespace panix\mod\shop\widgets\filters;

use panix\engine\web\AssetBundle;

/**
 * Class FilterAsset
 * @package panix\mod\shop\widgets\filtersnew\assets
 */
class FilterAsset extends AssetBundle
{

    public $sourcePath = __DIR__.'/assets';

    public $js = [
        'js/filter.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
