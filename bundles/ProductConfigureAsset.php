<?php

namespace panix\mod\shop\bundles;

use panix\engine\web\AssetBundle;

/**
 * Class WebAsset
 * @package panix\mod\shop\assets
 */
class ProductConfigureAsset extends AssetBundle
{

    public $sourcePath = __DIR__.'/../assets';

    public $js = [
        'js/product.view.configurations.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
