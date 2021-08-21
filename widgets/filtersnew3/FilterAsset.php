<?php

namespace panix\mod\shop\widgets\filtersnew3;

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
