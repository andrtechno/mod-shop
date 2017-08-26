<?php

namespace panix\mod\shop;

use Yii;
use panix\engine\WebModule;

class Module extends WebModule {

    public $routes = [
         '<url>' => 'shop/category/view',
        'product/<url>' => 'shop/default/view',
       
        ['class' => 'panix\mod\shop\components\ShopCategoryUrlRule', 'pattern' => '<url>', 'route' => 'shop/category/view']
    ];

    public function getNav() {
        return [
            [
                'label' => 'Станицы',
                "url" => ['/admin/shop'],
                'icon' => 'icon-shopcart'
            ],
            [
                'label' => Yii::t('shop/admin', 'CATEGORIES'),
                "url" => ['/admin/shop/category'],
                'icon' => 'icon-folder-open'
            ],
            [
                'label' => Yii::t('shop/admin', 'CURRENCY'),
                "url" => ['/admin/shop/currency'],
                'icon' => 'icon-currencies'
            ],
            [
                'label' => Yii::t('shop/admin', 'MANUFACTURER'),
                "url" => ['/admin/shop/manufacturer'],
                'icon' => 'icon-apple'
            ],
            [
                'label' => Yii::t('app', 'SETTINGS'),
                "url" => ['/admin/shop/settings'],
                'icon' => 'icon-settings'
            ]
        ];
    }

    public function getInfo() {
        return [
            'name' => Yii::t('shop/default', 'MODULE_NAME'),
            'author' => 'andrew.panix@gmail.com',
            'version' => '1.0',
            'icon' => 'icon-shopcart',
            'description' => Yii::t('shop/default', 'MODULE_DESC'),
            'url' => ['/admin/shop'],
        ];
    }

    protected function getDefaultModelClasses() {
        return [
            'ShopProduct' => 'panix\mod\shop\models\ShopProduct',
            'ShopProductSearch' => 'panix\mod\shop\models\search\ShopProductSearch',
            'ShopCurrency' => 'panix\mod\shop\models\ShopCurrency',
            'ShopCurrencySearch' => 'panix\mod\shop\models\search\ShopCurrencySearch',
            'ShopCategory' => 'panix\mod\shop\models\ShopCategory',
            'ShopCategorySearch' => 'panix\mod\shop\models\search\ShopCategorySearch',
            'ShopManufacturer' => 'panix\mod\shop\models\ShopManufacturer',
            'ShopManufacturerSearch' => 'panix\mod\shop\models\search\ShopManufacturerSearch',
            'ShopRelatedProduct' => 'panix\mod\shop\models\ShopRelatedProduct',
        ];
    }

}
