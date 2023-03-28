<?php

namespace panix\mod\shop;

use panix\engine\CMS;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\ProductImage;
use panix\mod\shop\models\ProductReviews;
use Yii;
use panix\engine\WebModule;
use yii\base\BootstrapInterface;
use app\web\themes\dashboard\sidebar\BackendNav;
use yii\web\UrlNormalizer;

class Module extends WebModule implements BootstrapInterface
{

    public $icon = 'shopcart';
    public $mailPath = '@shop/mail';
    public $searchAttribute = 'sku';
    public $filterViewCurrent = '@shop/widgets/filters/views/current';
    public $viewList = ['grid', 'list'];
    public $filterClass = 'panix\mod\shop\components\FilterLite';
    //public $filterClass = 'panix\mod\shop\components\FilterPro';

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {

        $rules['filter'] = 'shop/ajax/filter';
        $rules['nav'] = 'shop/ajax/test-nav';

        //$rules['catalog'] = 'shop/default/index';
        $rules['search/ajax'] = 'shop/search/ajax';
        $rules['notify/<id:\d+>'] = 'shop/notify/index';
        $rules['shop/ajax/currency/<id:\d+>'] = 'shop/ajax/currency';
        $rules['brand'] = 'shop/brand/index';
        $rules['product/<slug:[0-9a-zA-Z\-]+>-<id:\d+>'] = 'shop/product/view';
        $rules['product/<id:\d+>/review-add'] = 'shop/product/review-add';
        $rules['product/<id:\d+>/review-validate'] = 'shop/product/review-validate';
        $rules['product/<id:\d+>/<action:[0-9a-zA-Z_\-]+>'] = 'shop/product/<action>';
        $rules['product/tag/<tag:[\w\d\s]+>'] = 'shop/tag/view';

        $rules[] = [
            'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
            'route' => 'shop/brand/view',
            'index' => 'brand',
            'pattern' => 'brand/<slug:[0-9a-zA-Z_\-]+>/<params:.*>'
        ];
        $rules[] = [
            'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
            'route' => 'shop/brand/view',
            'index' => 'brand',
            'pattern' => 'brand/<slug:[0-9a-zA-Z_\-]+>'
        ];


        if ($app->id != 'console') {

            foreach ($this->getAllPaths() as $path) {

                $pattern = [];
                $pathNew = explode('/', $path);

                foreach ($pathNew as $pat) {
                    $pattern[] = '[0-9a-zA-Z_\-]+';
                }
                $pattern = implode('\/', $pattern);

                /* $rules22[] = [
                     'class' => 'panix\mod\shop\components\rules\CategoryUrlRule',
                     'route' => 'shop/catalog/view',
                     'defaults' => ['slug' => $path],
                     //'suffix'=>'.html',
                     'pattern' => "catalog/<slug:[0-9a-zA-Z_\-]+>",
                 ];*/

                //testing now
                $rules[] = [
                    'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                    'route' => 'shop/catalog/view',
                    'index' => 'catalog',
                    'pattern' => 'catalog/<slug:' . $path . '>/<params:.*>'
                ];
                $rules[] = [
                    'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                    'route' => 'shop/catalog/view',
                    'index' => 'catalog',
                    'pattern' => 'catalog/<slug:' . $path . '>'
                ];
            }

            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\SearchUrlRule',
                //'pattern'=>'products/search',
                'route' => 'shop/search/index',
                'defaults' => ['q' => Yii::$app->request->get('q')]
            ];

            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/sales',
                'index' => 'sales',
                'pattern' => 'sales/<params:.*>'
            ];
            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/sales',
                'index' => 'sales',
                'pattern' => 'sales'
            ];

            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/new',
                'index' => 'new',
                'pattern' => 'new/<params:.*>'
            ];
            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/new',
                'index' => 'new',
                'pattern' => 'new'
            ];
        }

        $app->urlManager->addRules(
            $rules,
            false
        );
        $app->setComponents([
            'currency' => ['class' => 'panix\mod\shop\components\CurrencyManager'],
        ]);


    }


    public function getAllPaths()
    {

        $tableName = Category::tableName();
        $dependency = new \yii\caching\DbDependency(['sql' => "SELECT MAX(updated_at) FROM {$tableName}"]);
        $allPaths = \Yii::$app->cache->get('CategoryUrlRule');
        if ($allPaths === false) {
            $items = (new \yii\db\Query())
                ->select(['id', 'full_path'])
                ->andWhere('id!=:id', [':id' => 1])
                ->from($tableName)
                ->all();

            $allPaths = [];
            foreach ($items as $item) {
                $allPaths[$item['id']] = $item['full_path'];
            }
            // Sort paths by length.
            uasort($allPaths, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            \Yii::$app->cache->set('CategoryUrlRule', $allPaths, Yii::$app->db->queryCacheDuration, $dependency);
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

    public function getReviewsCount()
    {
        return ProductReviews::find()->where(['status' => ProductReviews::STATUS_WAIT])->count();
    }

    public function getAdminMenu()
    {

        return [
            'shop' => [
                'label' => Yii::t('shop/default', 'MODULE_NAME'),
                'icon' => $this->icon,
                'sort' => 1,
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
                        'badge' => ($this->reviewsCount) ? $this->reviewsCount : '',
                        'visible' => (Yii::$app->user->can('/shop/admin/reviews/index') || Yii::$app->user->can('/shop/admin/reviews/*')) && Yii::$app->settings->get('shop', 'enable_reviews')
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
                        'label' => Yii::t('shop/admin', 'BRAND'),
                        "url" => ['/admin/shop/brand'],
                        'icon' => 'apple',
                        'visible' => Yii::$app->user->can('/shop/admin/brand/index') || Yii::$app->user->can('/shop/admin/brand/*')
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
        //return (new BackendNav())->findMenu($this->id)['items'];
        return Yii::$app->findMenu[$this->id]['items'];
    }

    public function getDefaultModelClasses()
    {
        return [
            'Product' => '\panix\mod\shop\models\Product',
            //'Product' => '\panix\mod\shop\models\ViewProduct',
            'Brand' => '\panix\mod\shop\models\Brand',
            'Category' => '\panix\mod\shop\models\Category',
            'ProductType' => '\panix\mod\shop\models\ProductType',
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
