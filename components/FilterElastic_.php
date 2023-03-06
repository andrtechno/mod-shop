<?php

namespace panix\mod\shop\components;

use panix\engine\api\ApiHelpers;
use panix\engine\CMS;
use panix\engine\Html;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductAttributesEav;
use panix\mod\shop\models\traits\EavQueryTrait;
use yii\base\BaseObject;
use yii\base\Component;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use panix\mod\shop\models\query\ProductQuery;
use yii\db\ActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\elasticsearch\Query as ElasticQuery;

class FilterElastic extends Component
{

    private $attributes;
    public $query;
    public $resultQuery;
    public $cacheDuration = 86400;

    public $controller;
    public $activeAttributes;
    public $price_min;
    public $price_max;
    //public $selectedPriceMin;
    //public $selectedPriceMax;
    public $cacheKey;

    public $min;
    public $max;


    /**
     * @var string min price in the query
     */
    private $_currentMinPrice = null;

    /**
     * @var string max price in the query
     */
    private $_currentMaxPrice = null;
    private $_eavAttributes;

    public $accessAttributes = [];

    /**
     * @var string
     */
    //public $_maxPrice, $_minPrice;
    public $prices;
    public $route;

    protected $_route;

    public function getActiveAttributes()
    {
        $data = [];
        $filters = (Yii::$app->request->post('filter')) ? Yii::$app->request->post('filter') : $_GET;

        foreach (array_keys($filters) as $key) {
            if (array_key_exists($key, $this->_eavAttributes)) {
                if ((boolean)$this->_eavAttributes[$key]->select_many === true) {
                    $data[$key] = (is_array($filters[$key])) ? $filters[$key] : explode(',', $filters[$key]);
                } else {
                    if (isset($filters[$key]))
                        $data[$key] = [$filters[$key]];
                }
            }
            if (isset($filters['brand'])) {
                $data['brand'] = $filters['brand'];
            }
        }

        return $data;
    }

    public function getActiveBrands()
    {
        $data = [];
        $post = Yii::$app->request->post('filter');

        if ($post && !in_array($this->route[0], ['/shop/brand/view'])) {
            $query = (isset($post['brand'])) ? $post['brand'] : Yii::$app->request->get('brand');
            if (is_array($query)) {
                $data = $query;
            } else {
                if (!empty($query))
                    $data = explode(',', $query);
            }

        }

        return $data;
    }

    public function setResultRoute($route)
    {
        $this->_route = $route;
    }

    public $q;

    public function __construct(ProductQuery $query = null, $config = [])
    {

        parent::__construct($config);
        $data = Yii::$app->request->post('filter');
        $slides = Yii::$app->request->post('slide');


        if ($query) {
            $this->resultQuery = clone $query;
            $this->query = clone $query;

            $this->setResultRoute($this->route);
            // $this->_route = $this->route;
            // $this->route = $this->getResultRoute();
            //  $this->setRoute($this->getActiveUrl());

            //  echo $this->resultQuery->createCommand()->rawSql;die;

            $this->attributes = $this->getEavAttributes();
            $this->activeAttributes = $this->getActiveAttributes();

            //Apply attributes
            $this->resultQuery->applyAttributes($this->activeAttributes);

            //Apply Brand's
            //$this->resultQuery->applyBrands($this->getActiveBrands());
            if (Yii::$app->request->get('brand') || isset($data['brand']) && Yii::$app->controller->route != '/shop/brand/view') {
                if (Yii::$app->request->get('brand')) {
                    $brands = explode(',', Yii::$app->request->get('brand', ''));
                } else {
                    $brands = $data['brand'];
                }
                $this->resultQuery->applyBrands($brands);
            }

            if (isset($slides['price'])) {
                $this->prices = $slides['price'];
            }
            if (Yii::$app->request->get('price')) {
                if (preg_match('/^[0-9\-]+$/', Yii::$app->request->get('price'))) {
                    $this->prices = explode('-', Yii::$app->request->get('price'));
                } else {
                    // $this->error404();
                }
            }
            $this->min = (int)floor($this->getMinPrice());
            $this->max = (int)ceil($this->getMaxPrice());
            if (($this->getCurrentMinPrice() != $this->min) || ($this->getCurrentMaxPrice() != $this->max)) {
                $this->resultQuery->applyRangePrices($this->getCurrentMinPrice(), $this->getCurrentMaxPrice());
            }

        } else {

            $this->query = Product::find();
            $this->query->andWhere(['!=', "availability", Product::STATUS_ARCHIVE]);
            $this->query->published();
            $this->query->applyCategories([5]);

            // $this->getEavAttributes();
        }
    }

    public function getActiveFilters()
    {
        $request = Yii::$app->request;
        // Render links to cancel applied filters like prices, brands, attributes.
        $menuItems = [];


        if (Yii::$app->controller->route == 'shop/catalog/view' || Yii::$app->controller->route == 'shop/search/index') {
            $brands = array_filter(explode(',', $request->getQueryParam('brand')));
            $brands = Brand::getDb()->cache(function ($db) use ($brands) {
                return Brand::findAll($brands);
            }, 3600);
        }

        if ($request->getQueryParam('price')) {

            if ($this->price_min && $this->price_max) {
                $menuItems['price'] = [
                    'name' => 'price',
                    'label' => Yii::t('shop/default', 'FILTER_BY_PRICE') . ':',
                    'itemOptions' => ['id' => 'current-filter-prices']
                ];
                //if ($this->price_min > 0 && $this->price_max) {
                //  print_r($this->getCurrentMinPrice());die;
                $menuItems['price']['items'][] = [
                    // 'name'=>'min_price',
                    'value_url' => number_format($this->price_min, 0, '', ''),
                    'value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()) . ' - ' . Yii::$app->currency->number_format($this->getCurrentMaxPrice()),
                    'label' => Html::decode(Yii::t('shop/default', 'FILTER_CURRENT_PRICE_MIN', ['value' => Yii::$app->currency->number_format($this->getCurrentMinPrice()) . ' до ' . Yii::$app->currency->number_format($this->getCurrentMaxPrice()), 'currency' => Yii::$app->currency->active['symbol']])),
                    'options' => ['class' => 'remove', 'data' => [
                        'type' => 'slider',
                        'slider-max' => round($this->price_max),
                        'slider-min' => round($this->price_min),
                        'slider-current-max' => $this->getCurrentMaxPrice(),
                        'slider-current-min' => $this->getCurrentMinPrice(),
                    ]],
                    'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'price', $this->getCurrentMinPrice() . '-' . $this->getCurrentMaxPrice())
                ];
            }
            // }
        }


        if (Yii::$app->controller->route == 'shop/catalog/view') {
            if (!empty($brands)) {
                $menuItems['brand'] = [
                    'name' => 'brand',
                    'label' => Yii::t('shop/default', 'FILTER_BY_BRAND') . ':',
                    'itemOptions' => ['id' => 'current-filter-brand']
                ];
                foreach ($brands as $id => $brand) {
                    $menuItems['brand']['items'][] = [
                        'value' => $brand->id,
                        'label' => $brand->name,
                        'options' => [
                            'class' => 'remove',
                            'data-type' => 'checkbox',
                            'data-name' => 'brand',
                            'data-target' => '#filter_brand_' . $brand->id
                        ],
                        'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, 'brand', $brand->id)
                    ];
                }
            }
        }

        // Process eav attributes
        $activeAttributes = $this->activeAttributes;

        if (!empty($activeAttributes)) {
            foreach ($activeAttributes as $attributeName => $value) {
                if (isset($this->eavAttributes[$attributeName])) {
                    $attribute = $this->eavAttributes[$attributeName];


                    $menuItems[$attributeName] = [
                        'name' => $attribute->name,
                        'label' => $attribute->title . ':',
                        'itemOptions' => ['id' => 'current-filter-' . $attribute->name],
                        'items' => []
                    ];
                    foreach ($attribute->options as $option) {
                        if (isset($activeAttributes[$attribute->name]) && in_array($option->id, $activeAttributes[$attribute->name])) {
                            $menuItems[$attributeName]['items'][] = [
                                'value' => $option->id,
                                'label' => $option->value . (($attribute->abbreviation) ? ' ' . $attribute->abbreviation : ''),
                                'options' => [
                                    'class' => 'remove',
                                    'data-type' => 'checkbox',
                                    'data-name' => $attribute->name,
                                    'data-target' => "#filter_{$attribute->name}_{$option->id}"
                                ],
                                'url' => Yii::$app->urlManager->removeUrlParam('/' . Yii::$app->requestedRoute, $attribute->name, $option->id)
                            ];
                            sort($menuItems[$attributeName]['items']);
                        }
                    }
                    //Если нет не одного итема, то делаем пустой пассив.
                    if (!$menuItems[$attributeName]['items']) {
                        $menuItems = [];
                    }

                }
            }
        }

        return $menuItems;
    }


    public function getAttributes()
    {

        $this->getEavAttributes();
        $active = $this->getActiveAttributes();
        $urlParams = [];
        $this->route = 'shop/catalog/view';
        if ($this->route === 'shop/catalog/view') {
            $category = Category::findOne(['full_path' => Yii::$app->request->get('slug')]);
            $urlParams['slug'] = $category->full_path;
        }

        foreach ($active as $key => $p) {
            $urlParams[$key] = implode(',', $p);
        }


        $elasticQuery = $this->getElasticQuery();
        $search = $elasticQuery->search();

        $opts = [];
        foreach ($search['aggregations']['options']['buckets'] as $opt) {
            $opts[$opt['key']] = $opt['doc_count'];
        }


        $this->getEavAttributes();
        $brands = $this->getCategoryBrands();
        //$first = array_key_first($active);
        //print_r($first);die;
        if ($brands) {
            $data['data']['brand'] = [
                'title' => Yii::t('shop/default', 'FILTER_BY_BRAND'),
                //'selectMany' => true,
                'type' => 3,
                'filters' => []
            ];

            foreach ($brands as $m) {
                $data['data']['brand']['filters'][] = [
                    'title' => $m['name'],
                    'count' => (int)$m['counter'],
                    'count_text' => (int)$m['counter'],
                    'id' => $m['brand_id'],
                    'slug' => $m['slug'],
                    'image' => $m['image'],
                ];
                sort($data['data']['brand']['filters']);
            }
            $data['data']['brand']['filtersCount'] = count($data['data']['brand']['filters']);
        }


        foreach ($this->_eavAttributes as $attribute) {
            $data['data'][$attribute->name] = [
                'title' => $attribute->title,
                'type' => (int)$attribute->type,
                //'key' => $attribute->name,
                'filters' => []
            ];

            $filtersCount = 0;
            foreach ($attribute->getOptions()->all() as $option) {
                $show = false;
                $count = 0;
                if (isset($opts[$option->id])) {
                    $count = $opts[$option->id];
                    $show = true;
                }


                $countText = $count;

                if (isset($active[$attribute->name])) {
                    if ((array_key_first($active) == $attribute->name) && $count) {
                        $first = $attribute->name;
                    }
                }
                //if ($show) {
                //if ($count > 0 && $show) {
                //if(count($active)>1){
                $data['data'][$attribute->name]['filters'][] = [
                    'title' => $option->value,
                    'count_text' => $countText,
                    'count' => (int)$count,
                    'key' => $attribute->name,
                    'id' => (int)$option->id,
                ];
                // }
            }
            $data['data'][$attribute->name]['filtersCount'] = count($data['data'][$attribute->name]['filters']);
            if ($attribute->sort == SORT_ASC) {
                sort($data['data'][$attribute->name]['filters']);
            } elseif ($attribute->sort == SORT_DESC) {
                rsort($data['data'][$attribute->name]['filters']);
            }
        }
        $data['totalCount'] = $elasticQuery->count();

        $data['url'] = ApiHelpers::url(Yii::$app->urlManager->addUrlParam('/' . $this->route, $urlParams));
        return $data;
    }

    public function getAttributesCallback($test = [])
    {

        $this->getEavAttributes();

        $elasticQuery = $this->getElasticQueryCallback($test);
        $search = $elasticQuery->search(null, ['size' => 0]);

        $opts = [];

        foreach ($search['aggregations']['filtered']['buckets'] as $keys => $item) {
            list($key, $id) = explode(':', $keys);
            $opts[$key][$id] = $item['doc_count'];
        }


        $data['data']['brand'] = [
            'title' => 'brand',
            'type' => (int)3,
            //'key' => $attribute->name,
            'filters' => []
        ];

        foreach ($this->getCategoryBrands() as $brand) {
            $count = 0;
            if (isset($opts['brand'][$brand['brand_id']])) {
                $count = $opts['brand'][$brand['brand_id']];

            }
            $countText = $count;
            $data['data']['brand']['filters'][] = [
                //'title' => $option->value,
                'count_text' => $countText,
                'count' => (int)$count,
                // 'key' => $attribute->name,
                'id' => (int)$brand['brand_id'],
            ];

        }


        foreach ($this->_eavAttributes as $attribute) {
            $data['data'][$attribute->name] = [
                'title' => $attribute->title,
                'type' => (int)$attribute->type,
                //'key' => $attribute->name,
                'filters' => []
            ];
            foreach ($attribute->getOptions()->all() as $option) {
                $count = 0;
                if (isset($opts[$attribute->name][$option->id])) {
                    $count = $opts[$attribute->name][$option->id];

                }
                $countText = $count;
                $data['data'][$attribute->name]['filters'][] = [
                    'title' => $option->value,
                    'count_text' => $countText,
                    'count' => (int)$count,
                    'key' => $attribute->name,
                    'id' => (int)$option->id,
                ];
            }
            $data['data'][$attribute->name]['filtersCount'] = count($data['data'][$attribute->name]['filters']);
        }
        $data['totalCount'] = '99999999'; //$elasticQuery->count();

        return $data;
    }

    public function getElasticQuery($min = 1)
    {
        $query = new ElasticQuery();
        $query->from('product');

        $query->fields = ["price"];
        $query->query = $this->q;
        $query->addAggregate('min_price', [
            'min' => ["field" => "price"],
        ]);
        $query->addAggregate('max_price', [
            'max' => ["field" => "price"],
        ]);
        $query->addAggregate('options', [
            'terms' => [
                "field" => "options",
                'order' => [
                    '_key' => 'desc',
                    //'_count' => 'desc'
                ],
                "min_doc_count" => $min,
                'size' => 9999
            ],

        ]);
        /*$query->addAggregate('options', [
            "filter" => ["term" => ["type" => "options.keyword"]],
            "aggs" => [
                "avg_price" => ["avg" => ["field" => "options.keyword"]],
               // "test" => ["term" => ["options" => 1]],
            ],

        ]);*/
        //$filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = ["term" => ['options' => $key]];
        /*$query->addAggregate('options', [
            "significant_terms" => [
                "field" => "options",
                //'terms'=>'options.keywords',
                "background_filter" => [
                    "bool" => [
                        "must_not" => [
                            [
                                "term" => [
                                    "event.outcome" => "failure"
                                ]
                            ]
                        ],
                        "filter" => [
                            [
                                "terms" => [
                                    "options" => [18,16],
                                ]
                            ]
                        ]
                    ]
                ],
                'size'=>9999,
                'min_doc_count'=>0
                //"p_value" => ["background_is_superset" => false, "normalize_above" => 1000]
            ]
        ]);*/
        /*$query->addAggregate('brands', [
            'terms' => [
                "field" => "brand_id",
                "min_doc_count" => $min,
                'size' => 9999
            ],

        ]);*/


        /*$query->addSuggester('my_suggest', [
            'text' => 'крос',
            'term' => [
                'field' => 'name',
            ]
        ]);*/

        return $query;
    }


    public function getElasticQueryCallback(array $start = [])
    {

        $active = $this->getActiveAttributes();

        $query = new ElasticQuery();
        $query->from('product');
        $query->fields = ["price"];
        $query->query = $this->q;
        $query->addAggregate('min_price', [
            'min' => ["field" => "price"],
        ]);
        $query->addAggregate('max_price', [
            'max' => ["field" => "price"],
        ]);
        /*$query->addAggregate('aptions', [
            "sampler" => [
                'shard_size'=>200
            ],
            'aggs'=>[
                'keywords'=>[
                    'significant_terms'=>[
                        'field'=>'options',
                        'size'=>9999,
                        'include'=>[212]
                    ]
                ]
            ]
        ]);*/
        /*$query->addAggregate('asssssptions', [

            "filters" => [
                'other_bucket' => false,
                'filters' => [
                    [
                        'terms' => ["options" => ["247"]]
                    ]
                ]
            ],
            "aggs" => [
                "terms" => [
                    "terms" => [
                        "field" => "options"
                   ]
                ]
            ]
        ]);*/
        $query->addAggregate('options', [
            "significant_terms" => [
                "field" => "options",
                //'terms'=>'options.keywords',
                "background_filter" => [
                    "bool" => [
                        /*"must" => [
                            [
                                "terms" => [
                                    "options" => [247]
                                ]
                            ]
                        ],*/
                        /*"must_not" => [
                            [
                                "term" => [
                                    "options" => "212"
                                ]
                            ]
                        ],*/

                        "filter" => [

                            [
                                "terms" => [
                                    "options" => [247],
                                ]
                            ]
                        ]
                    ]
                ],

                'size' => 9999,
                'min_doc_count' => 0,
                "p_value" => ["background_is_superset" => false, "normalize_above" => 10000]
            ]
        ]);
        $filtered = [];


        $slides = Yii::$app->request->post('slide');


        unset($start['brand']);
        foreach ($start as $attribute_key => $items) {

            foreach ($items as $key => $count) {
                //$action = ($attribute_key == 'brand') ? 'brand_id' : 'options';
                $filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = ["term" => ['options' => $key]];

                foreach ($active as $option_key => $options) {

                    if ($attribute_key != $option_key) {
                        //if ($attribute_key == 'brand') {
                        //    $filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = ["terms" => ['brand_id' => $options]];
                        //} else {
                        $filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = ["terms" => ['options' => $options]];
                        // }
                    }
                }
                if ($slides) {

                    foreach ($slides as $slide_key => $slide) {
                        if (isset($slide[0], $slide[1])) {

                            $filtered['filters']['filters'][$attribute_key . ':' . $key]['bool']['filter'][] = [
                                'range' => [
                                    $slide_key => ['gte' => $slide[0], 'lte' => $slide[1], 'boost' => 1.0]
                                ]
                            ];
                        }
                    }
                }
            }

        }
        //print_r($filtered);
        //die;
        //$query->addAggregate('filtered', $filtered);
        /*$query->addAggregate('filtered', [
            "filters" => [
                "filters" => [
                    27 => [
                        'bool' => [
                            'filter' => [
                                [
                                    "term" => ["options" => 27]
                                ],
                                [
                                    "terms" => [
                                        "options" => [80, 9, 2],
                                    ]
                                ]
                            ]
                        ]
                    ],
                    20 => [
                        'bool' => [
                            'filter' => [
                                [
                                    "term" => ["options" => 20]
                                ],
                                [
                                    "terms" => [
                                        "options" => [80, 9, 2],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);*/

        $search = $query->search(null, ['size' => 0]);

        return $query;
    }

    public function getEavAttributes()
    {
        if (is_array($this->_eavAttributes))
            return $this->_eavAttributes;


        $queryCategoryTypes = clone $this->query; //Product::find();
        $queryCategoryTypes->select(Product::tableName() . '.type_id');
        $queryCategoryTypes->groupBy(Product::tableName() . '.type_id');
        $queryCategoryTypes->distinct(true);
        $queryCategoryTypes->orderBy = false;

        $typesIds = $queryCategoryTypes->createCommand()->queryColumn();


        $query = Attribute::find()
            //->where(['IN', '`types`.`type_id`', $typesIds])
            ->where(['IN', '`type`.`type_id`', $typesIds])
            ->andWhere(['IN', 'type', [Attribute::TYPE_DROPDOWN, Attribute::TYPE_SELECT_MANY, Attribute::TYPE_CHECKBOX_LIST, Attribute::TYPE_RADIO_LIST, Attribute::TYPE_COLOR]])
            ->distinct(true)
            ->useInFilter()
            ->sort()
            ->orderBy(null)
            ->joinWith(['types type', 'options']);


        $result = $query->all();

        $this->_eavAttributes = [];
        foreach ($result as $attr) {
            $this->_eavAttributes[$attr->name] = $attr;
        }
        return $this->_eavAttributes;
    }


    public function getResultMaxPrice()
    {
        $res = clone $this->resultQuery;
        $result = $res->aggregatePrice('MAX')->orderBy(false)->asArray()->one();
        return (int)$result['aggregation_price'];
    }

    public function getResultMinPrice()
    {
        $res = clone $this->resultQuery;
        $result = $res->aggregatePrice('MIN')->orderBy(false)->asArray()->one();
        return (int)$result['aggregation_price'];
    }

    /**
     * @return string min price
     */
    public function getMinPrice()
    {
        $res = clone $this->query;
        $result = $res->aggregatePrice('MIN')->orderBy(false)->asArray()->one();
        if ($result) {
            return (int)$result['aggregation_price'];
        } else {
            return 0;
        }
    }

    /**
     * @return string max price
     */
    public function getMaxPrice()
    {
        $res = clone $this->query;
        $result = $res->aggregatePrice('MAX')->orderBy(false)->asArray()->one();
        if ($result) {
            return (int)$result['aggregation_price'];
        } else {
            return 0;
        }
    }


    /**
     * @return mixed
     */
    public function getCurrentMinPrice()
    {
        if ($this->_currentMinPrice !== null)
            return $this->_currentMinPrice;

        $this->_currentMinPrice = (isset($this->prices[0])) ? trim($this->prices[0]) : Yii::$app->currency->convert($this->price_min);

        return (int)$this->_currentMinPrice;
    }

    /**
     * @return string
     */
    public function getCurrentMaxPrice()
    {
        if ($this->_currentMaxPrice !== null)
            return $this->_currentMaxPrice;

        $this->_currentMaxPrice = (isset($this->prices[1])) ? trim($this->prices[1]) : Yii::$app->currency->convert($this->price_max);

        return (int)$this->_currentMaxPrice;
    }


    //быстрее работает.
    public function getCategoryBrands()
    {
        $this->query->orderBy = false;
        $queryClone = clone $this->query;
        $queryClone->addSelect(['brand_id', Product::tableName() . '.id']);
        $queryClone->joinWith([
            'brand' => function (\yii\db\ActiveQuery $query) {
                $query->andWhere([Brand::tableName() . '.switch' => 1]);
            },
        ]);

        $sub_query = clone $this->query;
        $sub_query->andwhere('`brand_id`=' . Brand::tableName() . '.`id`');
        $sub_query->select(['count(*)']);

        $queryClone->andWhere('brand_id IS NOT NULL');
        $queryClone->groupBy('brand_id');
        $queryClone->addSelect([
            'counter' => $sub_query,
            Brand::tableName() . '.`name_' . Yii::$app->language . '` as name',
            Brand::tableName() . '.slug as slug',
            Brand::tableName() . '.image as image'
        ]);
        $queryClone->cache($this->cacheDuration);

        $brands = $queryClone->createCommand()->queryAll();
        // echo $queryClone->createCommand()->rawSql;die;


        return $brands;
    }

}
