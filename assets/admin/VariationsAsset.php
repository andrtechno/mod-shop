<?php

/**
 *
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * @link http://pixelion.com.ua PIXELION CMS
 */

namespace panix\mod\shop\assets\admin;

use yii\web\AssetBundle;

class VariationsAsset extends AssetBundle {

    public $sourcePath = '@vendor/panix/mod-shop/assets/admin';
    public $jsOptions = array(
        'position' => \yii\web\View::POS_END //require for products.js   POS_END
    );
    public $js = [
        'js/products.variations.js',

    ];
}
