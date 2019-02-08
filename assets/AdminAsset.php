<?php
/**
 *
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * @link http://pixelion.com.ua PIXELION CMS
 */
namespace panix\mod\shop\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $sourcePath = '@vendor/panix/mod-shop/assets/admin';
    public $jsOptions = array(
        'position' => \yii\web\View::POS_END
    );
    public $js = [
        'js/relatedProductsTab.js',
        'js/products.js',
       // 'js/products.index.js',
    ];
    
 /* public $depends = [
        'panix\engine\assets\TinyMceAsset'
    ];*/
}
