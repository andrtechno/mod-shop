<?php

namespace panix\mod\shop;

use panix\mod\shop\models\Category;
use panix\mod\shop\models\ProductReviews;
use Yii;
use panix\engine\WebModule;
use yii\base\BootstrapInterface;
use panix\mod\admin\widgets\sidebar\BackendNav;

class Module extends WebModule implements BootstrapInterface
{

    public $icon = 'shopcart';
    public $mailPath = '@shop/mail';
    public $searchAttribute = 'sku';

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $rules['catalog'] = 'shop/default/index';
        $rules['search/ajax'] = 'shop/search/ajax';
        $rules['notify/<id:\d+>'] = 'shop/notify/index';
        $rules['shop/ajax/currency/<id:\d+>'] = 'shop/ajax/currency';
        $rules['manufacturer'] = 'shop/manufacturer/index';
        //$rules['manufacturer/<slug:[0-9a-zA-Z_\-]+>'] =  'shop/manufacturer/view';
        $rules['product/<slug:[0-9a-zA-Z\-]+>/<id:\d+>'] = 'shop/product/view';
        //$rules['product/<slug:[0-9a-zA-Z\-]+>'] = 'shop/product/view';
        $rules['product/<id:\d+>/review-add'] = 'shop/product/review-add';
        $rules['product/<id:\d+>/review-validate'] = 'shop/product/review-validate';
        $rules['product/<id:\d+>/<action:[0-9a-zA-Z_\-]+>'] = 'shop/product/<action>';


        if ($app->id != 'console') {

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
                'pattern' => 'manufacturer/<slug:[0-9a-zA-Z_\-]+>'
            ];
            /* $rules[] = [
                 'class' => 'panix\mod\shop\components\CategoryUrlRule',
                 'route' => 'shop/catalog/view',
                 'index' => 'catalog',
                 //'pattern'=>'catalog/<slug:[0-9a-zA-Z_\-]+>',
                 'alias' => 'full_path',
                 //  'pattern' => ''
             ];*/


            foreach ($this->getAllPaths() as $path) {
                $rules[] = [
                    'class' => 'panix\mod\shop\components\CategoryUrlRuleNew',
                    'route' => 'shop/catalog/view',
                    'defaults' => ['slug' => $path['full_path']],
                    //'suffix'=>'.html',
                    'pattern' => "catalog/<alias:[0-9a-zA-Z_\-]+>", ///<alias:[\w]+>
                ];

                /* $rules[] = [
                     'class' => 'panix\mod\shop\components\CategoryUrlRuleNew',
                     'route' => 'shop/catalog/view',
                     'defaults'=>['slug'=>$slug],
                     'pattern'=>"catalog/<alias:[0-9a-zA-Z_\-]+>/<filter:[\w,\/]+>", //
                     'encodeParams'=>false,
                 ];*/
                /* $rules[] = [
                     'class' => 'panix\mod\shop\components\CategoryUrlRuleNew',
                     'route' => 'shop/catalog/view',
                     'pattern'=>"catalog/<slug:[0-9a-zA-Z_\/\-]+>/<filter:[\w,\/]+>", //<filter:[\w-,\/]+>
                     'encodeParams'=>false,
                     //'mode'         => \yii\web\UrlRule::PARSING_ONLY,
                 ];*/
                /*$rules[] = [
                    'class' => 'panix\mod\shop\components\CategoryUrlRuleNew',
                    'route' => 'shop/catalog/view',
                    'pattern'=>"catalog/<slug:{$slug}>",
                    'encodeParams'=>false,
                    //'mode'         => \yii\web\UrlRule::PARSING_ONLY,
                ];*/

            }

            $rules[] = [
                'class' => 'panix\mod\shop\components\BaseTest2UrlRule',
                'route' => '/shop/catalog/new',
                'index' => 'new',
                'pattern' => 'new'
            ];

            $rules[] = [
                'class' => 'panix\mod\shop\components\BaseTest2UrlRule',
                'route' => '/shop/catalog/sales',
                'index' => 'sales',
                'pattern' => 'sales'
            ];
            /*$rules[] = [
                'class' => 'app\engine\BaseUrlRule',
                'route' => 'shop/catalog/best',
                'index' => 'best',
                'pattern' => 'best'
            ];*/
            /*$rules[] = [
                'class' => 'app\engine\BaseUrlRule',
                'route' => 'shop/catalog/discount',
                'index' => 'discount',
                'pattern' => 'discount'
            ];*/

            /*$rules[] = [
                'class' => 'panix\mod\shop\components\CategoryUrlRule',
            ];*/
        }

        $app->urlManager->addRules(
            $rules,
            false
        );
        $app->setComponents([
            'currency' => ['class' => 'panix\mod\shop\components\CurrencyManager'],
        ]);

    }

    protected function getAllPaths()
    {
        $allPaths = \Yii::$app->cache->get('CategoryUrlRule');
        if ($allPaths === false) {
            $allPaths = (new \yii\db\Query())
                ->select(['full_path'])
                ->andWhere('id!=:id', [':id' => 1])
                ->from(Category::tableName())
                ->all();


            // Sort paths by length.
            usort($allPaths, function ($a, $b) {
                return strlen($b['full_path']) - strlen($a['full_path']);
            });

            \Yii::$app->cache->set('CategoryUrlRule', $allPaths, 3600);
        }

        return $allPaths;
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

    /**
     * @param bool|int $current_id
     * @return array|\panix\mod\shop\models\Product[]
     */
    public function getViewProducts($current_id = false)
    {
        /** @var \panix\mod\shop\models\Product $productModel */
        $productModel = Yii::$app->getModule('shop')->model('Product');
        $list = [];
        $session = Yii::$app->session->get('views');
        if (!empty($session)) {
            $ids = array_unique($session);
            if ($current_id) {
                $key = array_search($current_id, $ids);
                unset($ids[$key]);
            }
            $list = $productModel::find()->where(['id' => $ids])->all();
        }
        return $list;
    }


    public function getAdminMenu()
    {
        return [
            'shop' => [
                'label' => Yii::t('shop/default', 'MODULE_NAME'),
                'icon' => $this->icon,
                'items' => [
                    [
                        'label' => Yii::t('shop/admin', 'PRODUCTS'),
                        // 'url' => ['/admin/shop'], //bug with bootstrap 4.2.x
                        'icon' => $this->icon,
                        'visible' => Yii::$app->user->can('/shop/admin/product/index') || Yii::$app->user->can('/shop/admin/product/*'),
                        'items' => [
                            [
                                'label' => Yii::t('shop/admin', 'PRODUCT_LIST'),
                                "url" => ['/admin/shop/product'],
                                'icon' => 'list',
                                'visible' => Yii::$app->user->can('/shop/admin/product/index') || Yii::$app->user->can('/shop/admin/product/*')
                            ],
                            [
                                'label' => Yii::t('shop/admin', 'CREATE_PRODUCT'),
                                "url" => ['/admin/shop/product/create'],
                                'icon' => 'add',
                                'visible' => Yii::$app->user->can('/shop/admin/product/create') || Yii::$app->user->can('/shop/admin/product/*')
                            ]
                        ]
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'CATEGORIES'),
                        "url" => ['/admin/shop/category'],
                        'icon' => 'folder-open',
                        'visible' => Yii::$app->user->can('/shop/admin/category/index') || Yii::$app->user->can('/shop/admin/category/*')
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'ATTRIBUTES'),
                        "url" => ['/admin/shop/attribute'],
                        'icon' => 'sliders',
                        'visible' => Yii::$app->user->can('/shop/admin/attribute/index') || Yii::$app->user->can('/shop/admin/attribute/*')
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'REVIEWS'),
                        "url" => ['/admin/shop/reviews'],
                        'icon' => 'comments',
                        'badge' => ProductReviews::find()->where(['status' => ProductReviews::STATUS_WAIT])->count(),
                        'visible' => Yii::$app->user->can('/shop/admin/reviews/index') || Yii::$app->user->can('/shop/admin/reviews/*')
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'TYPE_PRODUCTS'),
                        "url" => ['/admin/shop/type'],
                        'icon' => 't',
                        'visible' => Yii::$app->user->can('/shop/admin/type/index') || Yii::$app->user->can('/shop/admin/type/*')
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'NOTIFIER'),
                        "url" => ['/admin/shop/notify'],
                        'icon' => 'envelope',
                        'visible' => Yii::$app->user->can('/shop/admin/notify/index') || Yii::$app->user->can('/shop/admin/notify/*')
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'CURRENCY'),
                        "url" => ['/admin/shop/currency'],
                        'icon' => 'currencies',
                        'visible' => Yii::$app->user->can('/shop/admin/currency/index') || Yii::$app->user->can('/shop/admin/currency/*')
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'MANUFACTURER'),
                        "url" => ['/admin/shop/manufacturer'],
                        'icon' => 'apple',
                        'visible' => Yii::$app->user->can('/shop/admin/manufacturer/index') || Yii::$app->user->can('/shop/admin/manufacturer/*')
                    ],
                    [
                        'label' => Yii::t('shop/admin', 'SUPPLIER'),
                        "url" => ['/admin/shop/supplier'],
                        'icon' => 'supplier',
                        'visible' => Yii::$app->user->can('/shop/admin/supplier/index') || Yii::$app->user->can('/shop/admin/supplier/*')
                    ],
                    [
                        'label' => Yii::t('app/default', 'SETTINGS'),
                        "url" => ['/admin/shop/settings'],
                        'icon' => 'settings',
                        'visible' => Yii::$app->user->can('/shop/admin/settings/index') || Yii::$app->user->can('/shop/admin/settings/*')
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
        return (new BackendNav())->findMenu($this->id)['items'];
    }

    public function getDefaultModelClasses()
    {
        return [
            'Product' => '\panix\mod\shop\models\Product',
            'Manufacturer' => '\panix\mod\shop\models\Manufacturer',
            'Category' => '\panix\mod\shop\models\Category',
        ];
    }

    public function getInfo()
    {
        return [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'author' => $this->getAuthor(),
            'version' => '1.0',
            'icon' => $this->icon,
            'description' => Yii::t('shop/default', 'MODULE_DESC'),
            'url' => ['/admin/shop'],
        ];
    }

    public function getWidgetsList()
    {
        return [
            ''
        ];
    }

}
