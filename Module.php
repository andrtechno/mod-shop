<?php

namespace panix\shop;

use Yii;
use panix\engine\WebModule;

class Module extends WebModule {


    public $routes = [
        'product/<url>' => 'shop/default/view',
    ];

    public function getNav() {
        return [
            [
                'label' => 'Станицы',
                "url" => ['/admin/shop'],
                'icon' => 'icon-shopcart'
            ],
            [
                'label' => Yii::t('shop/admin','CATEGORIES'),
                "url" => ['/admin/shop/category'],
                'icon' => 'icon-folder-open'
            ],
            [
                'label' => Yii::t('shop/admin','CURRENCY'),
                "url" => ['/admin/shop/currency'],
                'icon' => 'icon-currencies'
            ],
            [
                'label' => Yii::t('shop/admin','MANUFACTURER'),
                "url" => ['/admin/shop/manufacturer'],
                'icon' => 'icon-apple'
            ],
            [
                'label' => Yii::t('app','SETTINGS'),
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
            'ShopProduct' => 'panix\shop\models\ShopProduct',
            'ShopProductSearch' => 'panix\shop\models\search\ShopProductSearch',
            'ShopCurrency' => 'panix\shop\models\ShopCurrency',
            'ShopCurrencySearch' => 'panix\shop\models\search\ShopCurrencySearch',
            'ShopCategory' => 'panix\shop\models\ShopCategory',
            'ShopCategorySearch' => 'panix\shop\models\search\ShopCategorySearch',
            'ShopManufacturer' => 'panix\shop\models\ShopManufacturer',
            'ShopManufacturerSearch' => 'panix\shop\models\search\ShopManufacturerSearch',
        ];
    }

}
