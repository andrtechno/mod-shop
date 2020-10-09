<?php

namespace panix\mod\shop\bundles;

use panix\engine\web\AssetBundle;

/**
 * Class WebAsset
 * @package panix\mod\shop\assets
 */
class WebAsset extends AssetBundle
{

    public $sourcePath = __DIR__ . '/../assets';
    /*
      public $js = [
      'js/relatedProductsTab.js',
      'js/products.js',
      // 'js/products.index.js',
      ]; */

    public $js = [
        'js/switchCurrency.js',
    ];


    public $depends = [
        'panix\mod\cart\CartAsset',
        'panix\mod\wishlist\WishlistAsset',
    ];

}
