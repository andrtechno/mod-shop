<?php

namespace panix\mod\shop;

use Yii;
use panix\engine\WebModule;

class Module extends WebModule {

    public $routes = [

        'product/<seo_alias>' => 'shop/default/view',
        'manufacturer/<seo_alias>' => 'shop/manufacturer/index',
        'shop/ajax/activateCurrency/<id>' => 'shop/ajax/activateCurrency',
        ['class' => 'panix\mod\shop\components\CategoryUrlRule'],
    ];

    public function getAdminMenu() {
        return array(
            'shop' => array(
                'label' => 'dsadsadsa',
                'icon' => 'dsadsadsa',
                'items' => array(
                    array(
                        'label' => Yii::t('app', 'PRODUCTS'),
                        'url' => array('/admin/shop/products/index'),
                    ),
                ),
            ),
        );
    }

    public function getAdminSidebarMenu() {
        Yii::import('mod.admin.widgets.EngineMainMenu');
        $mod = new EngineMainMenu;
        $items = $mod->findMenu($this->id);
        return $items['items'];
    }

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
                'label' => Yii::t('shop/admin', 'ATTRIBUTES'),
                "url" => ['/admin/shop/attribute'],
                'icon' => 'icon-filter'
            ],
            [
                'label' => Yii::t('shop/admin', 'TYPE_PRODUCTS'),
                "url" => ['/admin/shop/type'],
                'icon' => 'icon-t'
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
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
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
