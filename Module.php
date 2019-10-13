<?php

namespace panix\mod\shop;

use panix\mod\admin\widgets\sidebar\BackendNav;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Manufacturer;
use Yii;
use panix\engine\WebModule;
use yii\base\BootstrapInterface;

class Module extends WebModule implements BootstrapInterface
{

    public $icon = 'shopcart';

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {


        $rules['catalog'] = 'shop/default/index';
        $rules['search/ajax'] = 'shop/search/ajax';
        $rules['shop/notify'] = 'shop/notify/index';
        $rules['shop/ajax/currency/<id:\d+>'] = 'shop/ajax/currency';
        $rules['manufacturer'] = 'shop/manufacturer/index';
        //$rules['manufacturer/<slug:[0-9a-zA-Z\-]+>'] =  'shop/manufacturer/view';
        $rules['product/<slug:[0-9a-zA-Z\-]+>'] = 'shop/product/view';


        if (!($app instanceof \panix\engine\console\Application)) {
            $rules[] = [
                'class' => 'panix\mod\shop\components\SearchUrlRule',
                //'pattern'=>'products/search',
                'route' => 'shop/search/index',
                'defaults' => ['q' => Yii::$app->request->get('q')]
            ];

            $rules[] = [
                'class' => 'panix\mod\shop\components\ManufacturerUrlRule',
                'route' => 'shop/manufacturer/view',
                'index' => 'manufacturer',
           ];
            $rules[] = [
                'class' => 'panix\mod\shop\components\CategoryUrlRule',
                'route' => 'shop/catalog/view',
                'index' => 'catalog',
                'alias' => 'full_path',
            ];

            /*$rules[] = [
                'class' => 'panix\mod\shop\components\CategoryUrlRule',
            ];*/
        }

        $app->urlManager->addRules(
            $rules,
            true
        );
        $app->setComponents([
            'currency' => ['class' => 'panix\mod\shop\components\CurrencyManager'],
        ]);

    }

    public function init()
    {
        if (Yii::$app->id == 'console') {
            $this->controllerNamespace = 'panix\mod\shop\commands';
        }
        if (!(Yii::$app instanceof \yii\console\Application)) {
            parent::init();
        }


    }

    public function getAdminMenu()
    {
        return [
            'shop' => [
                'label' => 'Магазин',
                'icon' => $this->icon,
                'items' => [
                    [
                        'label' => 'Товары',
                        // 'url' => ['/admin/shop'], //bug with bootstrap 4.2.x
                        'icon' => $this->icon,
                        'items' => [
                            [
                                'label' => Yii::t('shop/admin', 'LIST'),
                                "url" => ['/admin/shop/product'],
                                'icon' => 'list',
                            ],
                            [
                                'label' => Yii::t('shop/admin', 'CREATE_PRODUCT'),
                                "url" => ['/admin/shop/product/create'],
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
                        'icon' => 'sliders'
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'TYPE_PRODUCTS'),
                        "url" => ['/admin/shop/type'],
                        'icon' => 't'
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'NOTIFIER'),
                        "url" => ['/admin/shop/notify'],
                        'icon' => 'envelope'
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
                        'label' => Yii::t('shop/admin', 'SUPPLIER'),
                        "url" => ['/admin/shop/supplier'],
                        'icon' => 'supplier'
                    ],
                    [
                        'label' => Yii::t('app', 'SETTINGS'),
                        "url" => ['/admin/shop/settings'],
                        'icon' => 'settings'
                    ],
                    'integration' => [
                        'label' => 'Интеграция',
                        'icon' => 'refresh',
                        'items' => []
                    ],
                ],
            ],
        ];
    }

    public function getAdminSidebar()
    {
        /*return [
            [
                'label' => Yii::t('app', 'SETTINGS'),
                "url" => ['/shop/settings'],
                'icon' => 'settings'
            ],
            'integration' => [
                'label' => 'Интеграция',
                'icon' => $this->icon,
                'items' => [
                    [
                        'label' => Yii::t('app', 'SETTINGS'),
                        "url" => ['/shop/settings'],
                        'icon' => 'settings'
                    ],
                ]
            ],
            'integration2' => [
                'label' => 'Интеграция',
                'icon' => $this->icon,
                'items' => [
                    [
                        'label' => Yii::t('app', 'SETTINGS'),
                        "url" => ['/shop/settings'],
                        'icon' => 'settings'
                    ],
                    [
                        'label' => Yii::t('app', 'SETTINGS'),
                        "url" => ['/shop/settings'],
                        'icon' => 'settings'
                    ],
                ]
            ],
        ];*/

        return (new BackendNav())->findMenu($this->id)['items'];
    }


    public function getInfo()
    {
        return [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'author' => 'andrew.panix@gmail.com',
            'version' => '1.0',
            'icon' => $this->icon,
            'description' => Yii::t('shop/default', 'MODULE_DESC'),
            'url' => ['/admin/shop'],
        ];
    }

    public function getWidgetsList(){
        return [
          ''
        ];
    }

}
