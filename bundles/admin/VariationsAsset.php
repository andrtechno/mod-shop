<?php

/**
 *
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * @link http://pixelion.com.ua PIXELION CMS
 */

namespace panix\mod\shop\bundles\admin;


use panix\engine\web\AssetBundle;

class VariationsAsset extends AssetBundle {

    public $sourcePath = __DIR__.'/../../assets/admin';
    public $js = [
        'js/products.variations.js',

    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
