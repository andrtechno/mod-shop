<?php

namespace panix\mod\shop;

use Yii;
use panix\engine\WebModule;
use yii\web\UrlRule;

class Module extends WebModule {

    public $icon = 'shopcart';
    public $routes = [
        'shop/ajax/currency/<id:\d+>' => 'shop/ajax/currency',
        'shop' => 'shop/default/index',
        'product/<seo_alias:\w+>' => 'shop/product/view',
        'manufacturer/<seo_alias:\w+>' => 'shop/manufacturer/view',

        'products/search/<q:\w+>' => 'shop/category/search',
        ['class' => 'panix\mod\shop\components\CategoryUrlRule'],
    ];




    public function init()
    {
        //yii <module_id>/<command>/<sub_command>
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\shop\commands';
        }
    }

    public function getAdminMenu() {
        return [
            'shop' => [
                'label' => 'Магазин',
                'icon' => $this->icon,
                'items' => [
                    [
                        'label' => 'Товары',
                        'url' => ['/admin/shop'],
                        'icon' => $this->icon,
                        'items' => [
                            [
                                'label' => Yii::t('shop/admin', 'LIST'),
                                "url" => ['/admin/shop/product'],
                                'icon' => 'list',
                            ],
                            [
                                'label' => Yii::t('shop/admin', 'CREATE_PRODUCT'),
                                "url" => ['/admin/default/create'],
                                'icon' => 'add',
                            ]
                        ]
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'CATEGORIES'),
                        "url" => ['/admin/shop/category'],
                        'icon' => 'folder-open'
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'ATTRIBUTES'),
                        "url" => ['/admin/shop/attribute'],
                        'icon' => 'filter'
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'TYPE_PRODUCTS'),
                        "url" => ['/admin/shop/type'],
                        'icon' => 't'
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'CURRENCY'),
                        "url" => ['/admin/shop/currency'],
                        'icon' => 'currencies'
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'MANUFACTURER'),
                        "url" => ['/admin/shop/manufacturer'],
                        'icon' => 'apple'
                    ],
                    [
                        'label' => Yii::t('app', 'SETTINGS'),
                        "url" => ['/admin/shop/settings'],
                        'icon' => 'settings'
                    ]
                ],
            ],
        ];
    }

    public function getAdminSidebar() {
        $mod = new \panix\engine\bootstrap\Nav;
        $items = $mod->findMenu($this->id);
        return $items['items'];
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


}
