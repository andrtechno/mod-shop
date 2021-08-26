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
    public $filterViewCurrent = '@shop/widgets/filtersnew/views/current';
    public $reviewsCount = 0;
    public $viewList = ['grid','list'];
    public function getImage($dirtyAlias)
    {
        //Get params
        $params = $data = $this->parseImageAlias($dirtyAlias);

        $alias = $params['alias'];
        $size = $params['size'];

        $imageQuery = ProductImage::find();

        $image = $imageQuery
            ->where(['urlAlias' => $alias])
            ->one();
        //if (!$image) {
        //    return $this->getPlaceHolder();
        //}

        return $image;
    }

    public function parseImageAlias($parameterized)
    {
        $params = explode('_', $parameterized);

        if (count($params) == 1) {
            $alias = $params[0];
            $size = null;
        } elseif (count($params) == 2) {
            $alias = $params[0];
            $size = $this->parseSize($params[1]);
            if (!$size) {
                $alias = null;
            }
        } else {
            $alias = null;
            $size = null;
        }


        return ['alias' => $alias, 'size' => $size];
    }


    /**
     *
     * Parses size string
     * For instance: 400x400, 400x, x400
     *
     * @param $notParsedSize
     * @return array|null
     */
    public function parseSize($notParsedSize)
    {
        $sizeParts = explode('x', $notParsedSize);
        $part1 = (isset($sizeParts[0]) and $sizeParts[0] != '');
        $part2 = (isset($sizeParts[1]) and $sizeParts[1] != '');
        if ($part1 && $part2) {
            if (intval($sizeParts[0]) > 0 &&
                intval($sizeParts[1]) > 0
            ) {
                $size = [
                    'width' => intval($sizeParts[0]),
                    'height' => intval($sizeParts[1])
                ];
            } else {
                $size = null;
            }
        } elseif ($part1 && !$part2) {
            $size = [
                'width' => intval($sizeParts[0]),
                'height' => null
            ];
        } elseif (!$part1 && $part2) {
            $size = [
                'width' => null,
                'height' => intval($sizeParts[1])
            ];
        } else {
            throw new \Exception('Something bad with size, sorry!');
        }

        return $size;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {

        $rules['catalog'] = 'shop/default/index';
        $rules['search/ajax'] = 'shop/search/ajax';
        $rules['notify/<id:\d+>'] = 'shop/notify/index';
        $rules['shop/ajax/currency/<id:\d+>'] = 'shop/ajax/currency';
        $rules['brand'] = 'shop/brand/index';
        //$rules['brand/<slug:[0-9a-zA-Z_\-]+>'] =  'shop/brand/view';

        $rules['product/<slug:[0-9a-zA-Z\-]+>-<id:\d+>'] = 'shop/product/view';
        //$rules['product/<slug:[0-9a-zA-Z\-]+>'] = 'shop/product/view';
        $rules['product/<id:\d+>/review-add'] = 'shop/product/review-add';
        $rules['product/<id:\d+>/review-validate'] = 'shop/product/review-validate';
        $rules['product/<id:\d+>/<action:[0-9a-zA-Z_\-]+>'] = 'shop/product/<action>';
        $rules['product/image/<action:[0-9a-zA-Z_\-]+>/<dirtyAlias:\w.+>'] = 'shop/image/<action>';


        if ($app->id != 'console') {
            $this->reviewsCount = ProductReviews::find()->where(['status' => ProductReviews::STATUS_WAIT])->count();
            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\SearchUrlRule',
                //'pattern'=>'products/search',
                'route' => 'shop/search/index',
                'defaults' => ['q' => Yii::$app->request->get('q')]
            ];

            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BrandUrlRule',
                'route' => 'shop/brand/view',
                'index' => 'brand',
                'pattern' => 'brand/<slug:[0-9a-zA-Z_\-]+>'
            ];
            /* $rules[] = [
                 'class' => 'panix\mod\shop\components\CategoryUrlRule',
                 'route' => 'shop/catalog/view',
                 'index' => 'catalog',
                 //'pattern'=>'catalog/<slug:[0-9a-zA-Z_\-]+>',
                 'alias' => 'full_path',
                 //  'pattern' => ''
             ];*/

          // $rules['sales/page/<page:\d+>/per-page/<per-page:\d+>'] = 'shop/catalog/sales';


            foreach ($this->getAllPaths() as $path) {
                $rules[] = [
                    'class' => 'panix\mod\shop\components\rules\CategoryUrlRule',
                    'route' => 'shop/catalog/view',
                    'defaults' => ['slug' => $path],
                    //'suffix'=>'.html',
                    'pattern' => "catalog/<alias:[0-9a-zA-Z_\-]+>", ///<alias:[\w]+>
                ];


                $rules[] = [
                    'class' => 'panix\mod\shop\components\rules\CategoryUrlRule',
                    'route' => 'shop/catalog/sales',
                    'defaults' => ['slug' => $path],
                    'index'=>'sales',
                    //'suffix'=>'.html',
                    'pattern' => "sales/<alias:[0-9a-zA-Z_\-]+>", ///<alias:[\w]+>
                ];


            }
            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/sales',
                'index' => 'sales',
                'pattern' => 'sales/page/<page:\d+>/per-page/<per-page:\d+>',
            ];
            $rules['sales/page/<page:\d+>'] = 'shop/catalog/sales';
            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/sales',
                'index' => 'sales',
                'pattern' => 'sales',
            ];
          //  $rules['sales'] = 'shop/catalog/sales';









            $rules[] = [
                'class' => 'panix\mod\shop\components\rules\BaseUrlRule',
                'route' => 'shop/catalog/new',
                'index' => 'new',
                'pattern' => 'new'
            ];

            /////////////////////////////////////////////

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
        return (new BackendNav())->findMenu($this->id)['items'];
    }

    public function getDefaultModelClasses()
    {
        return [
            'Product' => '\panix\mod\shop\models\Product',
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
