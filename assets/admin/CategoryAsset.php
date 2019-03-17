<?php
/**
 *
 * @author CORNER CMS <dev@corner-cms.com>
 * @link http://www.corner-cms.com/
 */
namespace panix\mod\shop\assets\admin;



use panix\engine\web\AssetBundle;

class CategoryAsset extends AssetBundle
{
    public $sourcePath = '@vendor/panix/mod-shop/assets/admin';
    public $js = [
        'js/category.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
